<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueCalled implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Queue $queueEntry)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('queue-display')];
    }

    public function broadcastAs(): string
    {
        return 'QueueCalled';
    }

    public function broadcastWith(): array
    {
        return [
            'queue_number' => $this->queueEntry->queue_number,
            'meja' => $this->queueEntry->meja->nama_meja,
        ];
    }
}
