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

    public function test_display_state_returns_the_latest_and_all_waiting_queues(): void
    {
        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);

        $called = Queue::create([
            'queue_number' => '001',
            'meja_id' => $meja->id,
            'status' => 'called',
            'called_at' => now(),
        ]);
        $waiting1 = Queue::create([
            'queue_number' => '002',
            'meja_id' => $meja->id,
            'status' => 'waiting',
        ]);
        $waiting2 = Queue::create([
            'queue_number' => '003',
            'meja_id' => $meja->id,
            'status' => 'waiting',
        ]);

        $this->getJson(route('display.state'))
            ->assertOk()
            ->assertJsonPath('latest.id', $called->id)
            ->assertJsonPath('waiting.0.id', $waiting1->id)
            ->assertJsonPath('waiting.1.id', $waiting2->id)
            ->assertJsonCount(2, 'waiting');
    }

    public function test_queue_called_event_is_broadcast_immediately(): void
    {
        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $queue = Queue::create([
            'queue_number' => '001',
            'meja_id' => $meja->id,
            'status' => 'called',
        ]);

        $event = new QueueCalled($queue);

        $this->assertInstanceOf(ShouldBroadcastNow::class, $event);
        $this->assertSame([
            'queue_id' => $queue->id,
            'queue_number' => '001',
            'meja' => 'Meja 01',
            'meja_id' => $meja->id,
            'nomor_meja' => 1,
            'layanan' => null,
        ], $event->broadcastWith());
    }
}
