<?php

namespace Tests\Feature;

use App\Events\QueueCalled;
use App\Events\QueueCreated;
use App\Events\QueueUpdated;
use App\Models\Meja;
use App\Models\User;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class QueueFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_service_generates_global_sequential_numbers_for_all_mejas(): void
    {
        Event::fake([QueueCreated::class]);

        $meja1 = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $meja4 = Meja::create(['nomor_meja' => 4, 'nama_meja' => 'Meja 04']);

        $service = app(QueueService::class);

        $q1 = $service->createFor($meja1);
        $this->assertSame('001', $q1->queue_number);
        $this->assertSame($meja1->id, $q1->meja_id);

        $q2 = $service->createFor($meja4);
        $this->assertSame('002', $q2->queue_number);
        $this->assertSame($meja4->id, $q2->meja_id);

        $q3 = $service->createFor($meja1);
        $this->assertSame('003', $q3->queue_number);

        Event::assertDispatched(QueueCreated::class, 3);
    }

    public function test_operator_cannot_update_a_queue_for_another_meja(): void
    {
        $first = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $second = Meja::create(['nomor_meja' => 2, 'nama_meja' => 'Meja 02']);

        $queue = app(QueueService::class)->createFor($second);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $first->id]);

        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), ['action' => 'skip'])->assertForbidden();
    }

    public function test_operator_state_returns_only_active_queues_for_their_meja(): void
    {
        Event::fake([QueueCreated::class]);

        $first = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $second = Meja::create(['nomor_meja' => 2, 'nama_meja' => 'Meja 02']);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $first->id]);

        $visibleQueue = app(QueueService::class)->createFor($first);
        app(QueueService::class)->createFor($second);

        $this->actingAs($operator)
            ->getJson(route('operator.queues.state'))
            ->assertOk()
            ->assertJsonCount(1, 'queues')
            ->assertJsonPath('queues.0.id', $visibleQueue->id)
            ->assertJsonPath('queues.0.status', 'waiting');
    }

    public function test_operator_can_call_and_replay_queue_via_post_and_patch(): void
    {
        Event::fake([
            QueueCalled::class,
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

        Event::assertDispatched(QueueCalled::class, 2);
    }

    public function test_operator_can_skip_and_complete_queue_via_post_and_patch(): void
    {
        Event::fake([
            QueueCalled::class,
            QueueUpdated::class,
        ]);

        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $meja->id]);
        $queue1 = app(QueueService::class)->createFor($meja);
        $queue2 = app(QueueService::class)->createFor($meja);

        // Waiting queues must be called before they can be skipped/completed.
        $this->actingAs($operator)
            ->postJson(route('operator.queues.update', $queue1), ['action' => 'call'])
            ->assertOk();

        $this->actingAs($operator)
            ->postJson(route('operator.queues.update', $queue2), ['action' => 'call'])
            ->assertOk();

        // Skip via POST after call
        $resSkip = $this->actingAs($operator)
            ->postJson(route('operator.queues.update', $queue1), ['action' => 'skip']);
        $resSkip->assertOk();
        $this->assertSame('skipped', $queue1->fresh()->status);

        // Complete via PATCH after call
        $resComplete = $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue2), ['action' => 'complete']);
        $resComplete->assertOk();
        $this->assertSame('completed', $queue2->fresh()->status);

        Event::assertDispatched(QueueUpdated::class, 2);
    }

    public function test_operator_actions_follow_the_queue_status_flow(): void
    {
        Event::fake([QueueCalled::class, QueueUpdated::class]);

        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $meja->id]);
        $queue = app(QueueService::class)->createFor($meja);

        $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue), ['action' => 'replay'])
            ->assertUnprocessable();

        $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue), ['action' => 'skip'])
            ->assertUnprocessable();

        $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue), ['action' => 'complete'])
            ->assertUnprocessable();

        $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue), ['action' => 'call'])
            ->assertOk();

        $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue), ['action' => 'call'])
            ->assertUnprocessable();

        $this->actingAs($operator)
            ->patchJson(route('operator.queues.update', $queue), ['action' => 'replay'])
            ->assertOk();
    }
}
