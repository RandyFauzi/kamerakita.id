<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\Partner;
use App\Models\RecruiterCommission;
use App\Models\User;
use App\Models\VideoWorkReport;
use App\Services\CalculatePartnerMetricsService;
use App\Services\CheckRecruiterMilestone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekruterDashboardTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekruter_dashboard_renders_successfully_with_zero_workers(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $rekruter = Partner::factory()->create([
            'user_id' => $user->id,
            'partner_role' => Partner::ROLE_REKRUTER,
            'full_name' => 'Rekruter Utama',
            'referral_code' => 'REF-TEST-RKR1',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_owner' => 'Rekruter Utama BCA',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Penagihan Reward Rekruter');
        $response->assertSee('REF-TEST-RKR1');
        $response->assertSee('Belum ada Worker yang terdaftar');
        $response->assertSee('Total Bonus Diterima');
        $response->assertSee('Bonus Siap Ditagihkan');
    }

    public function test_rekruter_metrics_and_dashboard_track_approved_videos_and_commissions(): void
    {
        $rekruterUser = User::factory()->create(['role' => 'user']);
        $rekruter = Partner::factory()->create([
            'user_id' => $rekruterUser->id,
            'partner_role' => Partner::ROLE_REKRUTER,
            'full_name' => 'Budi Rekruter',
            'referral_code' => 'REF-BUDI-01',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '9876543210',
            'bank_account_owner' => 'Budi Rekruter Mandiri',
        ]);

        // Worker 1: Will reach milestone (> 20 hours approved)
        // 5 approved VideoWorkReports = 12 hours (720 min)
        // 1 AtlasTask = 9 hours (540 min)
        // Total = 21 hours approved, 6 videos approved
        $worker1User = User::factory()->create(['email' => 'worker1@example.com']);
        $worker1 = Partner::factory()->create([
            'user_id' => $worker1User->id,
            'partner_role' => Partner::ROLE_WORKER,
            'recruiter_partner_id' => $rekruter->id,
            'full_name' => 'Worker Sukses',
            'mitra_id' => 'KMK-101',
            'email' => 'worker1@example.com',
        ]);

        for ($i = 0; $i < 5; $i++) {
            VideoWorkReport::factory()->create([
                'partner_id' => $worker1->id,
                'submission_date' => now()->subDays($i)->toDateString(),
                'project_name' => 'Project Video ' . $i,
                'submitted_duration_minutes' => 150,
                'approved_duration_minutes' => 144, // 144 * 5 = 720 mins = 12h
                'qc_status' => 'approved',
            ]);
        }

        $atlasWorker1 = AtlasWorker::create([
            'user_id' => $worker1User->id,
            'atlas_email' => 'worker1@example.com',
        ]);

        AtlasTask::create([
            'atlas_worker_id' => $atlasWorker1->id,
            'atlas_task_id' => 'TASK-001',
            'task_date' => now()->toDateString(),
            'task_name' => 'Video Review Task',
            'worked_minutes' => 540,
            'approved_minutes' => 540, // 9 hours
            'status' => 'APPROVED',
        ]);

        // Worker 2: In progress (< 20 hours approved)
        // 2 approved reports = 6 hours (360 min)
        $worker2User = User::factory()->create(['email' => 'worker2@example.com']);
        $worker2 = Partner::factory()->create([
            'user_id' => $worker2User->id,
            'partner_role' => Partner::ROLE_WORKER,
            'recruiter_partner_id' => $rekruter->id,
            'full_name' => 'Worker Pemula',
            'mitra_id' => 'KMK-102',
            'email' => 'worker2@example.com',
        ]);

        for ($i = 0; $i < 2; $i++) {
            VideoWorkReport::factory()->create([
                'partner_id' => $worker2->id,
                'submission_date' => now()->subDays($i)->toDateString(),
                'project_name' => 'Project Video W2 ' . $i,
                'submitted_duration_minutes' => 200,
                'approved_duration_minutes' => 180, // 180 * 2 = 360 mins = 6h
                'qc_status' => 'approved',
            ]);
        }

        $metricsService = app(CalculatePartnerMetricsService::class);
        $metrics = $metricsService->getRekruterMetrics($rekruter);

        // Check metrics structure & calculations
        $this->assertEquals(2, $metrics['recruited_workers_count']);
        // Total approved videos: Worker 1 (5 QC + 1 Atlas = 6) + Worker 2 (2 QC = 2) = 8
        $this->assertEquals(8, $metrics['total_approved_videos_count']);
        // Total approved minutes: 720 + 540 + 360 = 1620 minutes = 27 hours
        $this->assertEquals(1620, $metrics['total_approved_minutes']);

        // Check Milestone for Worker 1: 21h >= 20h -> auto creates 1 pending commission
        $this->assertEquals(1, $metrics['pending_commission_count']);
        $this->assertEquals(100000, $metrics['pending_commission_amount']);
        $this->assertEquals(0, $metrics['paid_commission_amount']);
        $this->assertCount(1, $metrics['claimable_workers']);
        $this->assertEquals('KMK-101', $metrics['claimable_workers'][0]['worker']->mitra_id);

        // Check Worker 1 breakdown
        $w1Data = collect($metrics['workers_data'])->firstWhere('worker.id', $worker1->id);
        $this->assertNotNull($w1Data);
        $this->assertEquals(6, $w1Data['approved_videos_count']);
        $this->assertEquals(21.0, $w1Data['approved_hours']);
        $this->assertTrue($w1Data['milestone_reached']);
        $this->assertEquals('pending', $w1Data['milestone_status']);

        // Check Worker 2 breakdown
        $w2Data = collect($metrics['workers_data'])->firstWhere('worker.id', $worker2->id);
        $this->assertNotNull($w2Data);
        $this->assertEquals(2, $w2Data['approved_videos_count']);
        $this->assertEquals(6.0, $w2Data['approved_hours']);
        $this->assertFalse($w2Data['milestone_reached']);

        // Test Dashboard View rendering with these metrics
        $this->actingAs($rekruterUser);
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Worker Sukses');
        $response->assertSee('Worker Pemula');
        $response->assertSee('KMK-101');
        $response->assertSee('KMK-102');
        $response->assertSee('Rp 100.000');
        $response->assertSee('Salin Format Penagihan');
        $response->assertSee('Kirim Tagihan ke WA Admin');
        $response->assertSee('Mandiri');
        $response->assertSee('9876543210');
    }

    public function test_paid_commission_is_reflected_in_bonus_diterima(): void
    {
        $rekruterUser = User::factory()->create(['role' => 'user']);
        $rekruter = Partner::factory()->create([
            'user_id' => $rekruterUser->id,
            'partner_role' => Partner::ROLE_REKRUTER,
            'full_name' => 'Siti Rekruter',
        ]);

        $workerUser = User::factory()->create(['email' => 'workersiti@example.com']);
        $worker = Partner::factory()->create([
            'user_id' => $workerUser->id,
            'partner_role' => Partner::ROLE_WORKER,
            'recruiter_partner_id' => $rekruter->id,
            'full_name' => 'Worker Rajin',
            'mitra_id' => 'KMK-999',
            'email' => 'workersiti@example.com',
        ]);

        // Pre-create paid commission
        RecruiterCommission::create([
            'recruiter_partner_id' => $rekruter->id,
            'worker_partner_id' => $worker->id,
            'approved_hours_at_milestone' => 20.5,
            'commission_amount' => 100000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $metricsService = app(CalculatePartnerMetricsService::class);
        $metrics = $metricsService->getRekruterMetrics($rekruter);

        $this->assertEquals(0, $metrics['pending_commission_count']);
        $this->assertEquals(0, $metrics['pending_commission_amount']);
        $this->assertEquals(1, $metrics['paid_commission_count']);
        $this->assertEquals(100000, $metrics['paid_commission_amount']);

        $this->actingAs($rekruterUser);
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Total Bonus Diterima');
        $response->assertSee('Komisi Lunas (Rp 100k)');
    }
}
