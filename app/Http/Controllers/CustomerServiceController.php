<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Meja;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // Urutkan meja secara otomatis: meja dengan antrean paling sedikit (termasuk 0 antrean) naik ke atas
        $mejas = Meja::with('layanan')
            ->withCount(['queues as waiting_count' => function ($query) use ($today) {
                $query->where('status', 'waiting')
                    ->where(function ($q) use ($today) {
                        $q->whereDate('created_at', $today)
                            ->orWhere('queue_date', $today);
                    });
            }])
            ->orderBy('waiting_count', 'asc')
            ->orderBy('nomor_meja', 'asc')
            ->get();

        $layanans = Layanan::where('is_active', true)->orderBy('nama_layanan')->get();

        return view('cs.index', [
            'mejas' => $mejas,
            'layanans' => $layanans,
        ]);
    }

    public function store(Request $request, Meja $meja, QueueService $queues)
    {
        $validated = $request->validate([
            'layanan_id' => ['nullable', 'exists:layanans,id'],
        ]);

        $layanan = ! empty($validated['layanan_id'])
            ? Layanan::find($validated['layanan_id'])
            : $meja->layanan;

        $queue = $queues->createFor($meja, $layanan);

        if ($request->expectsJson()) {
            return response()->json([
                'queue' => $queue,
                'meja' => $meja->nama_meja,
                'nomor_meja' => $meja->nomor_meja,
                'layanan' => $queue->layanan?->nama_layanan,
            ], 201);
        }

        return back()->with('queue', $queue->queue_number);
    }
}
