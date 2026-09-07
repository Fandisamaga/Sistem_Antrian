<?php

namespace Tests\Feature;

use App\Models\Meja;
use App\Models\QueueCounter;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_service_generates_sequential_global_numbers(): void
    {
        $meja = Meja::create(['nama_meja' => 'Meja 01']);
        QueueCounter::create(['id' => 1, 'current_number' => 0]);
        $service = app(QueueService::class);
        $this->assertSame('A-001', $service->createFor($meja)->queue_number);
        $this->assertSame('A-002', $service->createFor($meja)->queue_number);
    }

    public function test_operator_cannot_update_a_queue_for_another_meja(): void
    {
        $first = Meja::create(['nama_meja' => 'Meja 01']);
        $second = Meja::create(['nama_meja' => 'Meja 02']);
        QueueCounter::create(['id' => 1, 'current_number' => 0]);
        $queue = app(QueueService::class)->createFor($second);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $first->id]);
        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), ['action' => 'skip'])->assertForbidden();
    }
}
