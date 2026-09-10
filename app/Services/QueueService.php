<?php

namespace App\Services;

use App\Events\QueueCreated;
use App\Models\Layanan;
use App\Models\Meja;
use App\Models\Queue;
use App\Models\QueueCounter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Membuat nomor antrean berurutan untuk setiap meja (001, 002, dst.)
     * yang direset per hari, lalu ditautkan ke meja dan layanan yang dipilih.
     */
    public function createFor(Meja $meja, ?Layanan $layanan = null): Queue
    {
        return DB::transaction(function () use ($meja, $layanan): Queue {
            $today = Carbon::today()->toDateString();

            // Setiap meja memiliki counter sendiri untuk tiap hari.
            $counter = QueueCounter::query()
                ->where('meja_id', $meja->id)
                ->whereDate('queue_date', $today)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = QueueCounter::create([
                    'meja_id' => $meja->id,
                    'queue_date' => $today,
                    'current_number' => 0,
                ]);
            }

            $counter->increment('current_number');
            $counter->refresh();

            $queue = Queue::create([
                'queue_number' => str_pad((string) $counter->current_number, 3, '0', STR_PAD_LEFT),
                'queue_date' => $today,
                'meja_id' => $meja->id,
                'layanan_id' => $layanan?->id ?? $meja->layanan_id,
                'status' => 'waiting',
            ]);

            // Muat relasi meja & layanan
            $queue->load(['meja', 'layanan']);

            // Broadcast realtime ke display & meja operator
            event(new QueueCreated($queue));

            return $queue;
        }, 5);
    }
}
