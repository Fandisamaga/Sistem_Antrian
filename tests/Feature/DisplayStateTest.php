<?php

namespace Tests\Feature;

use App\Events\QueueCalled;
use App\Models\Meja;
use App\Models\Queue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_state_returns_the_latest_and_all_called_queues(): void
    {
        $meja = Meja::create(['nama_meja' => 'Meja 01']);

        $first = Queue::create([
            'queue_number' => 'A-001',
            'meja_id' => $meja->id,
            'status' => 'called',
        ]);
        $latest = Queue::create([
            'queue_number' => 'A-002',
            'meja_id' => $meja->id,
            'status' => 'called',
        ]);
        Queue::create([
            'queue_number' => 'A-003',
            'meja_id' => $meja->id,
            'status' => 'waiting',
        ]);

        $this->getJson(route('display.state'))
            ->assertOk()
            ->assertJsonPath('latest.id', $latest->id)
            ->assertJsonPath('called.0.id', $latest->id)
            ->assertJsonPath('called.1.id', $first->id)
            ->assertJsonCount(2, 'called');
    }

    public function test_queue_called_event_is_broadcast_immediately(): void
    {
        $meja = Meja::create(['nama_meja' => 'Meja 01']);
        $queue = Queue::create([
            'queue_number' => 'A-001',
            'meja_id' => $meja->id,
            'status' => 'called',
        ]);

        $event = new QueueCalled($queue);

        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
        $this->assertSame([
            'queue_id' => $queue->id,
            'queue_number' => 'A-001',
            'meja' => 'Meja 01',
        ], $event->broadcastWith());
    }
}
