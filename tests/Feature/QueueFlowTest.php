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
}
