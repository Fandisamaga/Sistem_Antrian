<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Queue $queue, public ?string $previousStatus = null) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('queue-display'),
            new Channel('meja.'.$this->queue->meja_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'QueueUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->queue->id,
            'queue_id' => $this->queue->id,
            'queue_number' => $this->queue->queue_number,
            'status' => $this->queue->status,
            'previous_status' => $this->previousStatus,
            'meja_id' => $this->queue->meja_id,
            'meja' => $this->queue->meja?->nama_meja,
            'nomor_meja' => $this->queue->meja?->nomor_meja,
            'layanan' => $this->queue->layanan?->nama_layanan,
        ];
    }
}

