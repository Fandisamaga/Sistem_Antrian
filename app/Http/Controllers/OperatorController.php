<?php

namespace App\Http\Controllers;

use App\Events\QueueCalled;
use App\Events\QueueUpdated;
use App\Models\Meja;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        $meja = $this->operatorMeja($request);

        return view('operator.index', [
            'meja' => $meja->load('layanan'),
            'queues' => $this->activeQueues($meja),
        ]);
    }

    public function state(Request $request)
    {
        $queues = $this->activeQueues($this->operatorMeja($request));

        return response()->json([
            'queues' => $queues->map(fn (Queue $queue) => [
                'id' => $queue->id,
                'queue_number' => $queue->queue_number,
                'status' => $queue->status,
                'layanan' => $queue->layanan?->nama_layanan,
                'created_at' => $queue->created_at?->format('H:i'),
            ])->values(),
        ]);
    }

    public function update(Request $request, Queue $queue)
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'admin' || $queue->meja_id === $user->meja_id,
            403,
        );

        $action = $request->validate([
            'action' => ['required', 'in:call,replay,skip,complete'],
        ])['action'];

        $allowedActions = match ($queue->status) {
            'waiting' => ['call'],
            'called' => ['replay', 'skip', 'complete'],
            default => [],
        };

        abort_unless(
            in_array($action, $allowedActions, true),
            422,
            'Aksi tidak tersedia untuk status antrean saat ini.',
        );

        $now = now();
        $previousStatus = $queue->status;

        if ($action === 'call') {
            $waitDuration = $queue->created_at ? (int) $queue->created_at->diffInSeconds($now) : null;

            // Proteksi atomik: hanya set 'called' dan hitung durasi tunggu jika status masih 'waiting'
            $affected = Queue::where('id', $queue->id)
                ->where('status', 'waiting')
                ->update([
                    'status' => 'called',
                    'operator_id' => $user->id,
                    'called_at' => $now,
                    'wait_duration' => $waitDuration,
                ]);

            abort_if($affected === 0, 409, 'Status antrean sudah berubah. Muat ulang halaman lalu coba lagi.');

            if ($affected > 0) {
                try {
                    event(new QueueCalled($queue->fresh(['meja', 'layanan'])));
                } catch (\Throwable $e) {
                    Log::warning('QueueCalled broadcast failed: '.$e->getMessage());
                }
            }
        }

        if ($action === 'replay') {
            // Simpan versi panggilan terakhir agar polling display juga dapat
            // mendeteksi panggil ulang bila koneksi WebSocket sempat putus.
            $queue->touch();

            try {
                event(new QueueCalled($queue->fresh(['meja', 'layanan'])));
            } catch (\Throwable $e) {
                Log::warning('QueueCalled replay broadcast failed: '.$e->getMessage());
            }
        }

        if ($action === 'skip') {
            $serveDuration = $queue->called_at ? (int) $queue->called_at->diffInSeconds($now) : null;

            $affected = Queue::where('id', $queue->id)
                ->where('status', 'called')
                ->update([
                    'status' => 'skipped',
                    'operator_id' => $user->id,
                    'completed_at' => $now,
                    'serve_duration' => $serveDuration,
                ]);

            abort_if($affected === 0, 409, 'Status antrean sudah berubah. Muat ulang halaman lalu coba lagi.');

            if ($affected > 0) {
                try {
                    event(new QueueUpdated($queue->fresh(['meja', 'layanan']), $previousStatus));
                } catch (\Throwable $e) {
                    Log::warning('QueueUpdated skip broadcast failed: '.$e->getMessage());
                }
            }
        }

        if ($action === 'complete') {
            $serveDuration = $queue->called_at ? (int) $queue->called_at->diffInSeconds($now) : null;

            $affected = Queue::where('id', $queue->id)
                ->where('status', 'called')
                ->update([
                    'status' => 'completed',
                    'operator_id' => $user->id,
                    'completed_at' => $now,
                    'serve_duration' => $serveDuration,
                ]);

            abort_if($affected === 0, 409, 'Status antrean sudah berubah. Muat ulang halaman lalu coba lagi.');

            if ($affected > 0) {
                try {
                    event(new QueueUpdated($queue->fresh(['meja', 'layanan']), $previousStatus));
                } catch (\Throwable $e) {
                    Log::warning('QueueUpdated complete broadcast failed: '.$e->getMessage());
                }
            }
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back();
    }

    private function operatorMeja(Request $request): Meja
    {
        $meja = $request->user()->meja;

        abort_unless($meja, 403, 'Akun operator ini belum ditautkan ke nomor meja pelayanan.');

        return $meja;
    }

    private function activeQueues(Meja $meja)
    {
        $today = Carbon::today()->toDateString();

        return $meja->queues()
            ->with('layanan')
            ->whereIn('status', ['waiting', 'called'])
            ->where(function ($query) use ($today) {
                $query->whereDate('created_at', $today)
                    ->orWhere('queue_date', $today);
            })
            ->oldest()
            ->get();
    }
}
