<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\Queue;
use App\Models\QueueCounter;
use Illuminate\Support\Facades\DB;

class QueueService
{
    /** Creates a globally unique number while holding a row-level database lock. */
    public function createFor(Meja $meja): Queue
    {
        return DB::transaction(function () use ($meja): Queue {
            $counter = QueueCounter::query()->lockForUpdate()->findOrFail(1);
            $counter->increment('current_number');

            return Queue::create([
                'queue_number' => 'A-'.str_pad((string) $counter->current_number, 3, '0', STR_PAD_LEFT),
                'meja_id' => $meja->id,
                'status' => 'waiting',
            ]);
        }, 5);
    }
}
