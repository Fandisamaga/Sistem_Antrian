<?php

namespace App\Services;

use App\Events\QueueCreated;
use App\Models\Layanan;
use App\Models\Meja;
use App\Models\Queue;
use App\Models\QueueCounter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueService
{
    /**
     * Membuat nomor antrean global (001, 002, dst.) untuk seluruh meja.
     * Nomor direset setiap hari, sedangkan meja dan layanan tetap tersimpan
     * pada tiket untuk menentukan tujuan pelayanannya.
     */
    public function createFor(Meja $meja, ?Layanan $layanan = null): Queue
    {
        $queue = DB::transaction(function () use ($meja, $layanan): Queue {
            $today = Carbon::today()->toDateString();

            // Satu counter per tanggal, tidak lagi dibedakan per meja.
            // createOrFirst aman bila dua CS membuat counter hari baru bersamaan.
            $counter = QueueCounter::query()->createOrFirst(
                ['queue_date' => $today],
                ['current_number' => 0],
            );

            $counter = QueueCounter::query()
                ->whereKey($counter)
                ->lockForUpdate()
                ->firstOrFail();

            $counter->increment('current_number');
            $counter->refresh();

            $queue = Queue::create([
                'queue_number' => str_pad((string) $counter->current_number, 3, '0', STR_PAD_LEFT),
                'queue_date' => $today,
                'meja_id' => $meja->id,
                'layanan_id' => $layanan?->id ?? $meja->layanan_id,
                'status' => 'waiting',
            ]);

            // Muat relasi meja & layanan untuk payload WebSocket.
            $queue->load(['meja', 'layanan']);

            return $queue;
        }, 5);

        // Broadcast hanya setelah transaksi tersimpan, agar semua klien
        // menerima tiket yang sudah benar-benar tersedia di database.
        try {
            event(new QueueCreated($queue));
        } catch (\Throwable $e) {
            Log::warning('QueueCreated broadcast failed: '.$e->getMessage());
        }

        return $queue;
    }
}
