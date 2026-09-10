<?php

namespace App\Http\Controllers;

use App\Events\QueueCalled;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        $meja = $request->user()->meja;

        abort_unless($meja, 403, 'Akun operator ini belum ditautkan ke nomor meja pelayanan.');

        $today = Carbon::today()->toDateString();

        return view('operator.index', [
            'meja' => $meja->load('layanan'),
            'queues' => $meja->queues()
                ->with('layanan')
                ->whereIn('status', ['waiting', 'called'])
                ->where(function ($q) use ($today) {
                    $q->whereDate('created_at', $today)
                        ->orWhere('queue_date', $today);
                })
                ->oldest()
                ->get(),
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

        $now = now();

        if ($action === 'call') {
            $waitDuration = $queue->created_at ? (int) $queue->created_at->diffInSeconds($now) : null;

            $queue->update([
                'status' => 'called',
                'operator_id' => $user->id,
                'called_at' => $now,
                'wait_duration' => $waitDuration,
            ]);

            event(new QueueCalled($queue->fresh(['meja', 'layanan'])));
        }

        if ($action === 'replay') {
            event(new QueueCalled($queue->load(['meja', 'layanan'])));
        }

        if ($action === 'skip') {
            $serveDuration = $queue->called_at ? (int) $queue->called_at->diffInSeconds($now) : null;

            $queue->update([
                'status' => 'skipped',
                'operator_id' => $user->id,
                'completed_at' => $now,
                'serve_duration' => $serveDuration,
            ]);
        }

        if ($action === 'complete') {
            $serveDuration = $queue->called_at ? (int) $queue->called_at->diffInSeconds($now) : null;

            $queue->update([
                'status' => 'completed',
                'operator_id' => $user->id,
                'completed_at' => $now,
                'serve_duration' => $serveDuration,
            ]);
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back();
    }
}
