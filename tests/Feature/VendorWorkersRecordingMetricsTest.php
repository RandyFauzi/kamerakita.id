<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Services\CalculatePartnerMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorWorkersRecordingMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_workers_page_displays_recording_only_billable_pending_and_reject_metrics(): void
    {
        $mainUser = User::factory()->create(['role' => 'main_account']);
        $mainAccount = Partner::factory()->create([
            'user_id' => $mainUser->id,
            'partner_role' => Partner::ROLE_MAIN_ACCOUNT,
            'email' => $mainUser->email,
        ]);

        $subUser = User::factory()->create([
            'role' => 'worker',
            'email' => 'subworker-rec@kamerakitaid.site',
        ]);
        $subWorker = Partner::factory()->create([
            'user_id' => $subUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mainAccount->id,
            'full_name' => 'Sub Akun Rekaman',
            'mitra_id' => 'KMK-8801',
            'email' => $subUser->email,
        ]);

        // Unrelated legacy VideoWorkReport should not leak into Billable/Pending/Reject recording stats
        VideoWorkReport::create([
            'partner_id' => $subWorker->id,
            'submission_date' => now()->toDateString(),
            'submitted_duration_minutes' => 600,
            'approved_duration_minutes' => 600,
            'qc_status' => 'approved',
            'payment_status' => 'paid',
            'evidence_email_image_path' => 'dummy.jpg',
        ]);

        $atlasWorker = AtlasWorker::create([
            'user_id' => $subUser->id,
            'atlas_email' => $subUser->email,
        ]);

        AtlasTask::create([
            'atlas_worker_id' => $atlasWorker->id,
            'atlas_task_id' => 'rec-101',
            'task_name' => 'Task Rekaman 1',
            'task_date' => now()->toDateString(),
            'worked_minutes' => 240,   // 4j 0m all time
            'approved_minutes' => 145, // 2j 25m billable
            'review_minutes' => 65,    // 1j 5m pending
            'rejected_minutes' => 30,  // 30m reject
            'billable_minutes' => 145,
            'status' => 'PARTIALLY_APPROVED',
        ]);

        $metrics = app(CalculatePartnerMetricsService::class)->getMitraMetrics($mainAccount);
        $workerRow = collect($metrics['workers_data'])->firstWhere('worker.id', $subWorker->id);

        $this->assertNotNull($workerRow);
        $this->assertEquals(240.0, $workerRow['metrics']['recording_worked_minutes']);
        $this->assertEquals(145.0, $workerRow['metrics']['recording_billable_minutes']);
        $this->assertEquals(65.0, $workerRow['metrics']['recording_pending_minutes']);
        $this->assertEquals(30.0, $workerRow['metrics']['recording_rejected_minutes']);
        $this->assertSame('4j 0m', $workerRow['metrics']['recording_worked_hours_formatted']);
        $this->assertSame('2j 25m', $workerRow['metrics']['recording_billable_hours_formatted']);
        $this->assertSame('1j 5m', $workerRow['metrics']['recording_pending_hours_formatted']);
        $this->assertSame('30m', $workerRow['metrics']['recording_rejected_hours_formatted']);

        $response = $this->actingAs($mainUser)->get(route('vendor.workers.index'));

        $response->assertOk()
            ->assertSee('All Time')
            ->assertSee('Billable')
            ->assertSee('Pending')
            ->assertSee('Reject')
            ->assertDontSee('Estimasi Pending Gaji')
            ->assertSee('2j 25m')
            ->assertSee('1j 5m')
            ->assertSee('30m');
    }

    public function test_mitra_team_page_also_displays_recording_only_columns(): void
    {
        $mitraUser = User::factory()->create(['role' => 'mitra']);
        $mitra = Partner::factory()->create([
            'user_id' => $mitraUser->id,
            'partner_role' => Partner::ROLE_MITRA,
            'email' => $mitraUser->email,
        ]);

        $workerUser = User::factory()->create(['role' => 'worker', 'email' => 'mitra-sub@kamerakitaid.site']);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'mitra_parent_id' => $mitra->id,
            'email' => $workerUser->email,
        ]);

        $atlasWorker = AtlasWorker::create([
            'user_id' => $workerUser->id,
            'atlas_email' => $workerUser->email,
        ]);

        AtlasTask::create([
            'atlas_worker_id' => $atlasWorker->id,
            'atlas_task_id' => 'rec-202',
            'task_name' => 'Task Mitra 1',
            'task_date' => now()->toDateString(),
            'worked_minutes' => 180,
            'approved_minutes' => 90,
            'review_minutes' => 45,
            'rejected_minutes' => 45,
            'billable_minutes' => 90,
            'status' => 'PARTIALLY_APPROVED',
        ]);

        $this->actingAs($mitraUser)
            ->get(route('vendor.workers.index'))
            ->assertOk()
            ->assertSee('Billable')
            ->assertSee('Reject')
            ->assertDontSee('Estimasi Pending Gaji')
            ->assertSee('1j 30m')
            ->assertSee('45m');
    }

    public function test_unlinked_worker_without_user_id_or_email_does_not_leak_other_atlas_tasks(): void
    {
        AtlasWorker::create([
            'user_id' => null,
            'atlas_email' => 'someone-else@kamerakitaid.site',
        ]);

        $orphan = Partner::factory()->create([
            'user_id' => null,
            'email' => '',
            'partner_role' => Partner::ROLE_WORKER,
        ]);

        $metrics = app(CalculatePartnerMetricsService::class)->getWorkerMetrics($orphan);

        $this->assertEquals(0.0, $metrics['recording_worked_minutes']);
        $this->assertEquals(0.0, $metrics['recording_billable_minutes']);
        $this->assertEquals(0.0, $metrics['recording_pending_minutes']);
        $this->assertEquals(0.0, $metrics['recording_rejected_minutes']);
    }
}
