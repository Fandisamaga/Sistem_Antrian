<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Queue $queue) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('queue-display'),
            new Channel('meja.'.$this->queue->meja_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'QueueCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->queue->id,
            'queue_number' => $this->queue->queue_number,
            'meja_id' => $this->queue->meja_id,
            'meja' => $this->queue->meja?->nama_meja,
            'layanan' => $this->queue->layanan?->nama_layanan,
            'status' => $this->queue->status,
            'created_at' => $this->queue->created_at->format('H:i:s'),
        ];
    }
}

