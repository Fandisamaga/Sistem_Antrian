<?php

namespace Tests\Feature;

use App\Models\Meja;
use App\Models\QueueCounter;
use App\Models\User;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_service_generates_sequential_numbers_per_meja(): void
    {
        $meja1 = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $meja4 = Meja::create(['nomor_meja' => 4, 'nama_meja' => 'Meja 04']);

        $service = app(QueueService::class);

        $q1 = $service->createFor($meja1);
        $this->assertSame('001', $q1->queue_number);
        $this->assertSame($meja1->id, $q1->meja_id);

        $q2 = $service->createFor($meja4);
        $this->assertSame('001', $q2->queue_number);
        $this->assertSame($meja4->id, $q2->meja_id);

        $q3 = $service->createFor($meja1);
        $this->assertSame('002', $q3->queue_number);
    }

    public function test_operator_cannot_update_a_queue_for_another_meja(): void
    {
        $first = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $second = Meja::create(['nomor_meja' => 2, 'nama_meja' => 'Meja 02']);

        $queue = app(QueueService::class)->createFor($second);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $first->id]);

        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), ['action' => 'skip'])->assertForbidden();
    }

    public function test_operator_can_call_and_replay_queue_via_post_and_patch(): void
    {
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\QueueCalled::class,
        ]);

        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $meja->id]);
        $queue = app(QueueService::class)->createFor($meja);

        // Call via POST
        $resPost = $this->actingAs($operator)
            ->postJson(route('operator.queues.update', $queue), ['action' => 'call']);
        $resPost->assertOk();
        $this->assertSame('called', $queue->fresh()->status);
        $this->assertSame($operator->id, $queue->fresh()->operator_id);

        // Replay via POST
        $resReplay = $this->actingAs($operator)
            ->postJson(route('operator.queues.update', $queue), ['action' => 'replay']);
        $resReplay->assertOk();

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\QueueCalled::class, 2);
    }

    public function test_operator_can_skip_and_complete_queue_via_post_and_patch(): void
    {
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\QueueUpdated::class,
        ]);

        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $meja->id]);
        $queue1 = app(QueueService::class)->createFor($meja);
        $queue2 = app(QueueService::class)->createFor($meja);

        // Skip via POST
        $resSkip = $this->actingAs($operator)
            ->postJson(route('operator.queues.update', $queue1), ['action' => 'skip']);
        $resSkip->assertOk();
        $this->assertSame('skipped', $queue1->fresh()->status);

        // Complete via PATCH
        $resComplete = $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue2), ['action' => 'complete']);
        $resComplete->assertOk();
        $this->assertSame('completed', $queue2->fresh()->status);

        \Illuminate\Support\Facades\Event::assertDispatched(\App\Events\QueueUpdated::class, 2);
    }
}
