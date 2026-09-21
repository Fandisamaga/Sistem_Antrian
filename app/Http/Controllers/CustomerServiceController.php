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

        // Antrean tetap dihitung setelah dipanggil atau dilewati. Jumlah baru
        // berkurang ketika operator menandai tiket sebagai selesai.
        $mejas = Meja::with('layanan')
            ->withCount(['queues as active_count' => function ($query) use ($today) {
                $query->whereIn('status', ['waiting', 'called', 'skipped'])
                    ->where(function ($q) use ($today) {
                        $q->whereDate('created_at', $today)
                            ->orWhere('queue_date', $today);
                    });
            }])
            ->orderBy('is_active', 'desc')
            ->orderBy('active_count', 'asc')
            ->orderBy('nomor_meja', 'asc')
            ->get();

        $layanans = Layanan::where('is_active', true)->orderBy('id', 'asc')->get();

        return view('cs.index', [
            'mejas' => $mejas,
            'layanans' => $layanans,
        ]);
    }

    public function store(Request $request, Meja $meja, QueueService $queues)
    {
        abort_if(! $meja->is_active, 422, 'Meja sedang tidak aktif / operator absen.');

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
