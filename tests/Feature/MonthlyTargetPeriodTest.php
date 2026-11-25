<?php

namespace Tests\Feature;

use App\Models\AtlasTask;
use App\Models\AtlasWorker;
use App\Models\User;
use App\Services\CalculatePartnerMetricsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyTargetPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_target_range_follows_16th_to_15th_rolling_cycle(): void
    {
        $service = app(CalculatePartnerMetricsService::class);

        // Day >= 16: 16 Nov 2026 - 15 Dec 2026
        $nov16 = $service->getMonthlyTargetRange(Carbon::parse('2026-11-16'));
        $this->assertEquals('2026-11-16', $nov16['start']->toDateString());
        $this->assertEquals('2026-12-15', $nov16['end']->toDateString());
        $this->assertEquals('16 Nov - 15 Dec 2026', $nov16['label']);

        $nov17 = $service->getMonthlyTargetRange(Carbon::parse('2026-11-17'));
        $this->assertEquals('2026-11-16', $nov17['start']->toDateString());
        $this->assertEquals('2026-12-15', $nov17['end']->toDateString());

        // Day <= 15 (still inside 16 Nov - 15 Dec cycle)
        $dec15 = $service->getMonthlyTargetRange(Carbon::parse('2026-12-15'));
        $this->assertEquals('2026-11-16', $dec15['start']->toDateString());
        $this->assertEquals('2026-12-15', $dec15['end']->toDateString());
        $this->assertEquals('16 Nov - 15 Dec 2026', $dec15['label']);

        // Cross-year: 16 Dec 2026 - 15 Jan 2027
        $dec16 = $service->getMonthlyTargetRange(Carbon::parse('2026-12-16'));
        $this->assertEquals('2026-12-16', $dec16['start']->toDateString());
        $this->assertEquals('2027-01-15', $dec16['end']->toDateString());
        $this->assertEquals('16 Dec 2026 - 15 Jan 2027', $dec16['label']);

        $jan15 = $service->getMonthlyTargetRange(Carbon::parse('2027-01-15'));
        $this->assertEquals('2026-12-16', $jan15['start']->toDateString());
        $this->assertEquals('2027-01-15', $jan15['end']->toDateString());

        // Month-end 31st -> Feb 15th without overflow
        $jan31 = $service->getMonthlyTargetRange(Carbon::parse('2027-01-31'));
        $this->assertEquals('2027-01-16', $jan31['start']->toDateString());
        $this->assertEquals('2027-02-15', $jan31['end']->toDateString());
        $this->assertEquals('16 Jan - 15 Feb 2027', $jan31['label']);
    }

    public function test_weekly_target_range_rolls_every_7_days_from_16th(): void
    {
        $service = app(CalculatePartnerMetricsService::class);

        // Week 1: 16 Nov - 22 Nov 2026
        $w1Start = $service->getWeeklyTargetRange(Carbon::parse('2026-11-16'));
        $this->assertEquals('2026-11-16', $w1Start['start']->toDateString());
        $this->assertEquals('2026-11-22', $w1Start['end']->toDateString());
        $this->assertEquals('16 Nov - 22 Nov 2026', $w1Start['label']);

        $w1Day2 = $service->getWeeklyTargetRange(Carbon::parse('2026-11-17'));
        $this->assertEquals('2026-11-16', $w1Day2['start']->toDateString());
        $this->assertEquals('2026-11-22', $w1Day2['end']->toDateString());

        $w1End = $service->getWeeklyTargetRange(Carbon::parse('2026-11-22'));
        $this->assertEquals('2026-11-16', $w1End['start']->toDateString());
        $this->assertEquals('2026-11-22', $w1End['end']->toDateString());

        // Week 2: 23 Nov - 29 Nov 2026
        $w2 = $service->getWeeklyTargetRange(Carbon::parse('2026-11-23'));
        $this->assertEquals('2026-11-23', $w2['start']->toDateString());
        $this->assertEquals('2026-11-29', $w2['end']->toDateString());
        $this->assertEquals('23 Nov - 29 Nov 2026', $w2['label']);

        // Week 3: 30 Nov - 06 Dec 2026
        $w3 = $service->getWeeklyTargetRange(Carbon::parse('2026-12-01'));
        $this->assertEquals('2026-11-30', $w3['start']->toDateString());
        $this->assertEquals('2026-12-06', $w3['end']->toDateString());
        $this->assertEquals('30 Nov - 06 Dec 2026', $w3['label']);

        // Week 4: 07 Dec - 13 Dec 2026
        $w4 = $service->getWeeklyTargetRange(Carbon::parse('2026-12-10'));
        $this->assertEquals('2026-12-07', $w4['start']->toDateString());
        $this->assertEquals('2026-12-13', $w4['end']->toDateString());

        // Remainder before cutoff: 14 Dec - 15 Dec 2026 (capped at 15th)
        $rem = $service->getWeeklyTargetRange(Carbon::parse('2026-12-14'));
        $this->assertEquals('2026-12-14', $rem['start']->toDateString());
        $this->assertEquals('2026-12-15', $rem['end']->toDateString());
        $this->assertEquals('14 Dec - 15 Dec 2026', $rem['label']);

        // Cross-year Week 3 in Dec cycle: 30 Dec 2026 - 05 Jan 2027
        $crossYear = $service->getWeeklyTargetRange(Carbon::parse('2027-01-02'));
        $this->assertEquals('2026-12-30', $crossYear['start']->toDateString());
        $this->assertEquals('2027-01-05', $crossYear['end']->toDateString());
        $this->assertEquals('30 Dec 2026 - 05 Jan 2027', $crossYear['label']);
    }

    public function test_biweekly_target_range_splits_16th_cycle_into_14_days_and_remainder(): void
    {
        $service = app(CalculatePartnerMetricsService::class);

        // First 2 weeks: 16 Nov - 29 Nov 2026
        $bw1Start = $service->getBiweeklyTargetRange(Carbon::parse('2026-11-16'));
        $this->assertEquals('2026-11-16', $bw1Start['start']->toDateString());
        $this->assertEquals('2026-11-29', $bw1Start['end']->toDateString());
        $this->assertEquals('16 Nov - 29 Nov 2026', $bw1Start['label']);

        $bw1Day10 = $service->getBiweeklyTargetRange(Carbon::parse('2026-11-25'));
        $this->assertEquals('2026-11-16', $bw1Day10['start']->toDateString());
        $this->assertEquals('2026-11-29', $bw1Day10['end']->toDateString());

        // Second 2 weeks: 30 Nov - 15 Dec 2026
        $bw2 = $service->getBiweeklyTargetRange(Carbon::parse('2026-11-30'));
        $this->assertEquals('2026-11-30', $bw2['start']->toDateString());
        $this->assertEquals('2026-12-15', $bw2['end']->toDateString());
        $this->assertEquals('30 Nov - 15 Dec 2026', $bw2['label']);

        // Cross-year 2nd half: 30 Dec 2026 - 15 Jan 2027
        $crossBw2 = $service->getBiweeklyTargetRange(Carbon::parse('2027-01-02'));
        $this->assertEquals('2026-12-30', $crossBw2['start']->toDateString());
        $this->assertEquals('2027-01-15', $crossBw2['end']->toDateString());
        $this->assertEquals('30 Dec 2026 - 15 Jan 2027', $crossBw2['label']);
    }

    public function test_global_metrics_sums_monthly_target_only_within_16th_to_15th_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 14:00:00'));

        try {
            $user = User::factory()->create();
            $worker = AtlasWorker::create([
                'user_id' => $user->id,
                'atlas_email' => 'target_test@example.com',
            ]);

            // Before cycle (15 Nov) -> MUST NOT be counted
            AtlasTask::create([
                'atlas_worker_id' => $worker->id,
                'atlas_task_id' => 'T-BEFORE',
                'task_name' => 'Before Cycle',
                'task_date' => '2026-11-15',
                'worked_minutes' => 120,
                'approved_minutes' => 120,
                'status' => 'APPROVED',
            ]);

            // Cycle Start (16 Nov) -> COUNTED (2h approved, 3h worked)
            AtlasTask::create([
                'atlas_worker_id' => $worker->id,
                'atlas_task_id' => 'T-START',
                'task_name' => 'Cycle Start',
                'task_date' => '2026-11-16',
                'worked_minutes' => 180,
                'approved_minutes' => 120,
                'status' => 'APPROVED',
            ]);

            // Next month before cutoff (15 Dec) -> COUNTED (1h approved, 1h worked)
            AtlasTask::create([
                'atlas_worker_id' => $worker->id,
                'atlas_task_id' => 'T-END',
                'task_name' => 'Cycle End',
                'task_date' => '2026-12-15',
                'worked_minutes' => 60,
                'approved_minutes' => 60,
                'status' => 'APPROVED',
            ]);

            // Next cycle (16 Dec) -> MUST NOT be counted
            AtlasTask::create([
                'atlas_worker_id' => $worker->id,
                'atlas_task_id' => 'T-AFTER',
                'task_name' => 'Next Cycle',
                'task_date' => '2026-12-16',
                'worked_minutes' => 240,
                'approved_minutes' => 240,
                'status' => 'APPROVED',
            ]);

            $metrics = app(CalculatePartnerMetricsService::class)->getGlobalMetrics();

            $this->assertEquals(450, $metrics['daily_target_hours']);
            $this->assertEquals('17 Nov 2026', $metrics['daily_period_label']);
            $this->assertEquals(0, $metrics['daily_approved_minutes']);
            $this->assertEquals(0.0, $metrics['daily_approved_hours']);
            $this->assertEquals(0.0, $metrics['daily_submitted_hours']);

            // Add today's task (17 Nov) -> counted in daily, weekly, and monthly
            AtlasTask::create([
                'atlas_worker_id' => $worker->id,
                'atlas_task_id' => 'T-TODAY',
                'task_name' => 'Today Work',
                'task_date' => '2026-11-17',
                'worked_minutes' => 2700, // 45h
                'approved_minutes' => 1350, // 22.5h
                'status' => 'APPROVED',
            ]);

            $metricsWithDaily = app(CalculatePartnerMetricsService::class)->getGlobalMetrics();
            $this->assertEquals(1350, $metricsWithDaily['daily_approved_minutes']);
            $this->assertEquals(22.5, $metricsWithDaily['daily_approved_hours']);
            $this->assertEquals(45.0, $metricsWithDaily['daily_submitted_hours']);
            $this->assertEquals(5.0, $metricsWithDaily['daily_progress_percent']);
            $this->assertEquals(10.0, $metricsWithDaily['daily_submitted_percent']);

            $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
            $this->actingAs($admin)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSeeText('Target Harian')
                ->assertSeeText('/ 450 Jam')
                ->assertSeeText('Target Mingguan')
                ->assertSeeText('/ 2.000 Jam')
                ->assertSeeText('Target Per 2 Minggu')
                ->assertSeeText('/ 4.000 Jam')
                ->assertSeeText('Target Bulanan')
                ->assertSeeText('/ 8.000 Jam')
                ->assertSeeText('22.5');

            $this->assertEquals(8000, $metricsWithDaily['monthly_target_hours']);
            $this->assertEquals(4000, $metricsWithDaily['biweekly_target_hours']);
            $this->assertEquals(2000, $metricsWithDaily['weekly_target_hours']);
            $this->assertEquals('16 Nov - 29 Nov 2026', $metricsWithDaily['biweekly_period_label']);
            $this->assertEquals(1470, $metricsWithDaily['biweekly_approved_minutes']); // T-START (120) + T-TODAY (1350)
            $this->assertEquals(24.5, $metricsWithDaily['biweekly_approved_hours']);
            $this->assertEquals('16 Nov - 22 Nov 2026', $metricsWithDaily['weekly_period_label']);
            $this->assertEquals(1470, $metricsWithDaily['weekly_approved_minutes']); // T-START (120) + T-TODAY (1350)
            $this->assertEquals(24.5, $metricsWithDaily['weekly_approved_hours']);
            $this->assertEquals(48.0, $metricsWithDaily['weekly_submitted_hours']);
            $this->assertEquals('16 Nov - 15 Dec 2026', $metricsWithDaily['monthly_period_label']);
            $this->assertEquals(1530, $metricsWithDaily['monthly_approved_minutes']); // 120 + 1350 + 60
            $this->assertEquals(25.5, $metricsWithDaily['monthly_approved_hours']);
            $this->assertEquals(49.0, $metricsWithDaily['monthly_submitted_hours']); // (180 + 2700 + 60) / 60
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_target_metrics_align_with_makassar_recording_dates_even_during_early_wita_morning(): void
    {
        // 2026-11-24 20:00 UTC == 2026-11-25 04:00 WITA
        Carbon::setTestNow(Carbon::parse('2026-11-24 20:00:00', 'UTC'));

        try {
            $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
            $worker = AtlasWorker::create(['user_id' => $admin->id, 'atlas_email' => 'wita_parity@example.com']);

            // Recorded at 2026-11-24 17:00:00 UTC == 2026-11-25 01:00:00 WITA
            AtlasTask::create([
                'atlas_worker_id' => $worker->id,
                'atlas_task_id' => 'T-WITA-EARLY',
                'task_name' => 'WITA Early Task',
                'task_date' => '2026-11-25',
                'recorded_at' => '2026-11-24 17:00:00',
                'worked_minutes' => 180,
                'approved_minutes' => 150,
                'status' => 'APPROVED',
            ]);

            $metrics = app(CalculatePartnerMetricsService::class)->getGlobalMetrics();
            $this->assertEquals('25 Nov 2026', $metrics['daily_period_label']);
            $this->assertEquals(150, $metrics['daily_approved_minutes']);
            $this->assertEquals(2.5, $metrics['daily_approved_hours']);
            $this->assertEquals(3.0, $metrics['daily_submitted_hours']);

            // Exact same stats on Admin Recordings filtered for 2026-11-25
            $recordings = $this->actingAs($admin)->get(route('admin.recordings.index', [
                'start_date' => '2026-11-25',
                'end_date' => '2026-11-25',
            ]));
            $recordings->assertOk();
            $stats = $recordings->viewData('stats');
            $this->assertEquals($metrics['daily_approved_minutes'], (int) round((float) $stats['total_approved']));
            $this->assertEquals(180, (int) round((float) $stats['total_worked']));
        } finally {
            Carbon::setTestNow();
        }
    }
}
