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

            $this->assertEquals('16 Nov - 15 Dec 2026', $metrics['monthly_period_label']);
            $this->assertEquals(180, $metrics['monthly_approved_minutes']); // 120 + 60
            $this->assertEquals(3.0, $metrics['monthly_approved_hours']);
            $this->assertEquals(4.0, $metrics['monthly_submitted_hours']); // (180 + 60) / 60
        } finally {
            Carbon::setTestNow();
        }
    }
}
