<?php

namespace Tests\Feature;

use App\Models\Layanan;
use App\Models\Meja;
use App\Models\Queue;
use App\Models\User;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $this->actingAs($operator)->get(route('admin.dashboard'))->assertForbidden();

        $cs = User::factory()->create(['role' => 'cs']);
        $this->actingAs($cs)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_access_dashboard_and_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.print'))->assertOk();
    }

    public function test_admin_can_create_and_update_layanan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.layanans.store'), [
            'nama_layanan' => 'Perekaman KTP-el',
            'kode_layanan' => 'KTP',
            'deskripsi' => 'Layanan biometrik',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.layanans.index'));
        $this->assertDatabaseHas('layanans', ['kode_layanan' => 'KTP']);

        $layanan = Layanan::where('kode_layanan', 'KTP')->first();

        $this->actingAs($admin)->put(route('admin.layanans.update', $layanan), [
            'nama_layanan' => 'KTP-el & IKD',
            'kode_layanan' => 'KTP',
            'deskripsi' => 'Update deskripsi',
            'is_active' => true,
        ])->assertRedirect(route('admin.layanans.index'));

        $this->assertDatabaseHas('layanans', ['nama_layanan' => 'KTP-el & IKD']);
    }

    public function test_admin_can_create_operator_and_meja(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create Meja
        $this->actingAs($admin)->post(route('admin.mejas.store'), [
            'nama_meja' => 'Meja 25',
            'nomor_meja' => 25,
        ])->assertRedirect(route('admin.operators.index'));

        $this->assertDatabaseHas('mejas', ['nomor_meja' => 25]);
        $meja = Meja::where('nomor_meja', 25)->first();

        // Create Operator
        $this->actingAs($admin)->post(route('admin.operators.store'), [
            'name' => 'Operator 25',
            'email' => 'operator25@dukcapil.test',
            'password' => 'password123',
            'meja_id' => $meja->id,
        ])->assertRedirect(route('admin.operators.index'));

        $this->assertDatabaseHas('users', ['email' => 'operator25@dukcapil.test', 'meja_id' => $meja->id]);
    }

    public function test_operator_call_and_complete_records_durations_and_operator_id(): void
    {
        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $operator = User::factory()->create(['role' => 'operator', 'meja_id' => $meja->id]);

        $service = app(QueueService::class);
        $queue = $service->createFor($meja);

        // Operator calls
        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), [
            'action' => 'call',
        ]);

        $queue->refresh();
        $this->assertSame('called', $queue->status);
        $this->assertSame($operator->id, $queue->operator_id);
        $this->assertNotNull($queue->called_at);

        // Operator completes
        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), [
            'action' => 'complete',
        ]);

        $queue->refresh();
        $this->assertSame('completed', $queue->status);
        $this->assertNotNull($queue->completed_at);
        $this->assertNotNull($queue->serve_duration);
    }

    public function test_cs_can_view_mejas_and_create_ticket(): void
    {
        $layanan = Layanan::firstOrCreate(['kode_layanan' => 'KK'], ['nama_layanan' => 'Kartu Keluarga']);
        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01', 'layanan_id' => $layanan->id]);
        $cs = User::factory()->create(['role' => 'cs']);

        $this->actingAs($cs)->get(route('cs.index'))
            ->assertOk()
            ->assertSee('Meja 01')
            ->assertSee('01');

        $response = $this->actingAs($cs)->postJson(route('cs.queues.store', $meja));
        $response->assertCreated()
            ->assertJsonPath('queue.queue_number', '001')
            ->assertJsonPath('meja', 'Meja 01')
            ->assertJsonPath('nomor_meja', 1)
            ->assertJsonPath('layanan', 'Kartu Keluarga');
    }

    public function test_reports_show_operator_performance_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $meja = Meja::create(['nomor_meja' => 1, 'nama_meja' => 'Meja 01']);
        $operator = User::factory()->create(['name' => 'Operator Hebat', 'role' => 'operator', 'meja_id' => $meja->id]);

        $service = app(QueueService::class);
        $queue = $service->createFor($meja);

        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), ['action' => 'call']);
        $this->actingAs($operator)->patch(route('operator.queues.update', $queue), ['action' => 'complete']);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));
        $response->assertOk()
            ->assertSee('Operator Hebat')
            ->assertSee('100%');
    }

    public function test_reports_support_preset_periods_and_breakdowns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $layanan = Layanan::firstOrCreate(['kode_layanan' => 'KK'], ['nama_layanan' => 'Kartu Keluarga']);
        $meja = Meja::create(['nomor_meja' => 2, 'nama_meja' => 'Meja 02']);
        $operator = User::factory()->create(['name' => 'Operator Dua', 'role' => 'operator', 'meja_id' => $meja->id]);

        $queue = Queue::create([
            'queue_number' => '001',
            'queue_date' => Carbon::today()->toDateString(),
            'meja_id' => $meja->id,
            'layanan_id' => $layanan->id,
            'operator_id' => $operator->id,
            'status' => 'completed',
            'wait_duration' => 60,
            'serve_duration' => 180,
            'called_at' => Carbon::now()->subMinutes(3),
            'completed_at' => Carbon::now(),
        ]);

        // Preset today
        $this->actingAs($admin)->get(route('admin.reports.index', ['periode' => 'today']))
            ->assertOk()
            ->assertSee('Kartu Keluarga')
            ->assertSee('Meja 02')
            ->assertSee('Operator Dua');

        // Preset week
        $this->actingAs($admin)->get(route('admin.reports.index', ['periode' => 'week']))
            ->assertOk()
            ->assertSee('Minggu Ini');

        // Preset month
        $this->actingAs($admin)->get(route('admin.reports.index', ['periode' => 'month']))
            ->assertOk()
            ->assertSee('Bulan Ini');

        // Print view
        $this->actingAs($admin)->get(route('admin.reports.print', ['periode' => 'today']))
            ->assertOk()
            ->assertSee('Kartu Keluarga')
            ->assertSee('Meja 02')
            ->assertSee('Operator Dua');
    }

    public function test_dashboard_displays_realtime_desk_and_service_monitoring(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $layanan = Layanan::firstOrCreate(['kode_layanan' => 'AK-LHR'], ['nama_layanan' => 'Akta Kelahiran']);
        $meja = Meja::create(['nomor_meja' => 3, 'nama_meja' => 'Meja 03']);

        Queue::create([
            'queue_number' => '001',
            'queue_date' => Carbon::today()->toDateString(),
            'meja_id' => $meja->id,
            'layanan_id' => $layanan->id,
            'status' => 'waiting',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk()
            ->assertSee('Akta Kelahiran')
            ->assertSee('Meja 03')
            ->assertSee('1 Antre');
    }
}
