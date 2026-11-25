<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CalculatePartnerMetricsService
{
    private const DEFAULT_WORKER_HOURLY_RATE_IDR = 60000;
    private const MITRA_COMMISSION_HOURLY_RATE_IDR = 10000;
    private const DEFAULT_MITRA_OWN_HOURLY_RATE_IDR = 60000;

    /**
     * Get dynamic metrics for a single Worker partner (cached 30s, isolated per partner).
     */
    public function getWorkerMetrics(Partner $worker): array
    {
        return app(DashboardCacheService::class)->remember(
            DashboardCacheService::workerKey($worker->id),
            DashboardCacheService::TTL_WORKER,
            fn () => $this->computeWorkerMetrics($worker)
        );
    }

    private function computeWorkerMetrics(Partner $worker): array
    {
        $allReports = VideoWorkReport::query()
            ->where('partner_id', $worker->id)
            ->get();

        $approvedReports = $allReports->where('qc_status', 'approved');

        // Worker Metrics via AtlasTask (recording-only source)
        $atlasWorkerIds = $this->resolveAtlasWorkerIds($worker);

        $recordingAgg = $atlasWorkerIds->isNotEmpty()
            ? \App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)
                ->selectRaw('COALESCE(SUM(worked_minutes), 0) as worked_minutes, COALESCE(SUM(approved_minutes), 0) as approved_minutes, COALESCE(SUM(review_minutes), 0) as review_minutes, COALESCE(SUM(rejected_minutes), 0) as rejected_minutes')
                ->first()
            : null;

        $totalSubmittedMinutes = (float) ($recordingAgg->worked_minutes ?? 0);
        $allTimeMinutes = (float) ($recordingAgg->approved_minutes ?? 0);
        $recordingBillableMinutes = (float) ($recordingAgg->approved_minutes ?? 0);
        $recordingPendingMinutes = (float) ($recordingAgg->review_minutes ?? 0);
        $recordingRejectedMinutes = (float) ($recordingAgg->rejected_minutes ?? 0);

        $todaySubmittedMinutes = $atlasWorkerIds->isNotEmpty()
            ? (float) (\App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)
                ->whereDate('task_date', now())
                ->sum('worked_minutes') ?? 0)
            : 0.0;

        $highestDailyMinutes = $atlasWorkerIds->isNotEmpty()
            ? (float) (\App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)
                ->selectRaw('SUM(worked_minutes) as daily_total')
                ->groupBy('task_date')
                ->get()
                ->max('daily_total') ?? 360)
            : 360.0;

        $paidMinutes = (float) $approvedReports->where('payment_status', 'paid')
            ->sum('approved_duration_minutes');
            
        $pendingMinutes = (float) $approvedReports->where('payment_status', 'unpaid')
            ->sum('approved_duration_minutes');

        $rate = $worker->effectiveHourlyRate();
        $paidEarnings = ($paidMinutes / 60) * $rate;
        $pendingEarnings = ($pendingMinutes / 60) * $rate;
        $totalEarnings = $paidEarnings + $pendingEarnings;

        return [
            'total_submitted_minutes' => round($totalSubmittedMinutes, 1),
            'today_submitted_minutes' => round($todaySubmittedMinutes, 1),
            'highest_daily_minutes' => round($highestDailyMinutes, 1),
            'all_time_minutes' => round($allTimeMinutes, 1),
            'paid_minutes' => round($paidMinutes, 1),
            'pending_minutes' => round($pendingMinutes, 1),
            'recording_worked_minutes' => round($totalSubmittedMinutes, 1),
            'recording_billable_minutes' => round($recordingBillableMinutes, 1),
            'recording_pending_minutes' => round($recordingPendingMinutes, 1),
            'recording_rejected_minutes' => round($recordingRejectedMinutes, 1),
            'total_submitted_hours_formatted' => $this->formatMinutes($totalSubmittedMinutes),
            'all_time_hours_formatted' => $this->formatMinutes($allTimeMinutes),
            'paid_hours_formatted' => $this->formatMinutes($paidMinutes),
            'pending_hours_formatted' => $this->formatMinutes($pendingMinutes),
            'recording_worked_hours_formatted' => $this->formatMinutes($totalSubmittedMinutes),
            'recording_billable_hours_formatted' => $this->formatMinutes($recordingBillableMinutes),
            'recording_pending_hours_formatted' => $this->formatMinutes($recordingPendingMinutes),
            'recording_rejected_hours_formatted' => $this->formatMinutes($recordingRejectedMinutes),
            'paid_earnings' => $paidEarnings,
            'pending_earnings' => $pendingEarnings,
            'total_earnings' => $totalEarnings,
            'hourly_rate' => $rate,
        ];
    }

    /**
     * Get aggregated metrics for a Mitra or Main Account managing multiple Workers (cached 5m, isolated per partner).
     */
    public function getMitraMetrics(Partner $mitra): array
    {
        return app(DashboardCacheService::class)->remember(
            DashboardCacheService::mitraKey($mitra->id),
            DashboardCacheService::TTL_MITRA,
            fn () => $this->computeMitraMetrics($mitra)
        );
    }

    private function computeMitraMetrics(Partner $mitra): array
    {
        // Get all workers assigned to this parent (Mitra or Main Account)
        $workers = Partner::query()
            ->where('mitra_parent_id', $mitra->id)
            ->where('partner_role', Partner::ROLE_WORKER)
            ->with('user')
            ->get();

        $workerIds = $workers->pluck('id');
        $reportsByPartner = $workerIds->isNotEmpty()
            ? VideoWorkReport::query()
                ->whereIn('partner_id', $workerIds)
                ->where('qc_status', 'approved')
                ->get()
                ->groupBy('partner_id')
            : collect();

        $atlasBatch = $this->buildBatchAtlasStats($workers, includeDailyExtremes: true);

        $workersData = [];
        $totalAllTime = 0;
        $totalPaid = 0;
        $totalPending = 0;
        $teamPaidEarnings = 0;
        $teamPendingEarnings = 0;

        // Passive commission from workers in Rupiah per approved hour (10k for Mitra, 0 for Main Account).
        $commissionRate = $mitra->teamCommissionHourlyRate();
        $commissionPaid = 0;
        $commissionPending = 0;

        foreach ($workers as $worker) {
            $metrics = $this->buildWorkerMetricsFromBatch(
                $worker,
                $reportsByPartner->get($worker->id, collect()),
                $atlasBatch[$worker->id] ?? $this->emptyAtlasBatchEntry()
            );

            $workersData[] = [
                'worker' => $worker,
                'metrics' => $metrics,
            ];

            $totalAllTime += $metrics['all_time_minutes'];
            $totalPaid += $metrics['paid_minutes'];
            $totalPending += $metrics['pending_minutes'];
            $teamPaidEarnings += $metrics['paid_earnings'];
            $teamPendingEarnings += $metrics['pending_earnings'];

            $commissionPaid += ($metrics['paid_minutes'] / 60) * $commissionRate;
            $commissionPending += ($metrics['pending_minutes'] / 60) * $commissionRate;
        }

        // Parent's own recordings use the configured partner rate, with a Rupiah fallback.
        $ownReports = VideoWorkReport::query()
            ->where('partner_id', $mitra->id)
            ->where('qc_status', 'approved')
            ->get();

        $ownAllTime = $ownReports->sum('approved_duration_minutes');
        $ownPaid = $ownReports->where('payment_status', 'paid')->sum('approved_duration_minutes');
        $ownPending = $ownReports->where('payment_status', 'unpaid')->sum('approved_duration_minutes');

        $mitraOwnRate = $mitra->base_hourly_rate ?: self::DEFAULT_MITRA_OWN_HOURLY_RATE_IDR;
        $personalPaidEarnings = ($ownPaid / 60) * $mitraOwnRate;
        $personalPendingEarnings = ($ownPending / 60) * $mitraOwnRate;

        return [
            'workers_count' => $workers->count(),
            'workers_data' => $workersData,

            // Team total metrics
            'total_all_time_minutes' => $totalAllTime,
            'total_paid_minutes' => $totalPaid,
            'total_pending_minutes' => $totalPending,
            'total_all_time_hours_formatted' => $this->formatMinutes($totalAllTime),
            'total_paid_hours_formatted' => $this->formatMinutes($totalPaid),
            'total_pending_hours_formatted' => $this->formatMinutes($totalPending),
            'sub_worker_hourly_rate' => $mitra->subWorkerHourlyRate(),
            'team_paid_earnings' => $teamPaidEarnings,
            'team_pending_earnings' => $teamPendingEarnings,
            'team_total_earnings' => $teamPaidEarnings + $teamPendingEarnings,

            // Mitra / Main Account own earnings
            'personal_paid_earnings' => $personalPaidEarnings,
            'personal_pending_earnings' => $personalPendingEarnings,
            'personal_all_time_hours_formatted' => $this->formatMinutes($ownAllTime),

            // Commission earnings (10k/hr for Mitra, 0 for Main Account)
            'commission_paid_earnings' => $commissionPaid,
            'commission_pending_earnings' => $commissionPending,
            'commission_hourly_rate' => $commissionRate,
            'personal_hourly_rate' => $mitraOwnRate,
        ];
    }

    /**
     * Get metrics for a Rekruter — tracks recruited workers and milestone commissions (cached 5m, isolated per partner).
     */
    public function getRekruterMetrics(Partner $rekruter): array
    {
        return app(DashboardCacheService::class)->remember(
            DashboardCacheService::rekruterKey($rekruter->id),
            DashboardCacheService::TTL_REKRUTER,
            fn () => $this->computeRekruterMetrics($rekruter)
        );
    }

    private function computeRekruterMetrics(Partner $rekruter): array
    {
        // Workers recruited via referral code
        $recruitedWorkers = \App\Models\Partner::where('recruiter_partner_id', $rekruter->id)
            ->with(['videoWorkReports'])
            ->get();

        // Commission records from recruiter_commissions table
        $commissions = \App\Models\RecruiterCommission::where('recruiter_partner_id', $rekruter->id)
            ->with('worker')
            ->get();

        $atlasBatch = $this->buildBatchAtlasStats($recruitedWorkers, includeDailyExtremes: false);

        $workersData = [];
        $totalApprovedVideosAll = 0;
        $totalApprovedMinutesAll = 0;

        foreach ($recruitedWorkers as $worker) {
            // 1. VideoWorkReport counts & minutes
            $qcApprovedReports = $worker->videoWorkReports->where('qc_status', 'approved');
            $qcApprovedVideosCount = $qcApprovedReports->count();
            $qcApprovedMinutes = (float) $qcApprovedReports->sum('approved_duration_minutes');
            $qcReportedMinutes = (float) $worker->videoWorkReports->sum(function ($r) {
                return $r->submitted_duration_minutes ?? $r->duration_minutes ?? 0;
            });
            $qcTotalVideosCount = $worker->videoWorkReports->count();

            // 2. AtlasTask counts & minutes (pre-aggregated in batch)
            $atlasAgg = $atlasBatch[$worker->id] ?? $this->emptyAtlasBatchEntry();

            $atlasApprovedVideosCount = (int) $atlasAgg['approved_count'];
            $atlasApprovedMinutes = (float) $atlasAgg['approved_minutes'];
            $atlasReportedMinutes = (float) $atlasAgg['worked_minutes'];
            $atlasPendingMinutes = (float) $atlasAgg['review_minutes'];
            $atlasRejectedMinutes = (float) $atlasAgg['rejected_minutes'];
            $atlasTotalVideosCount = (int) $atlasAgg['task_count'];

            // Combined metrics
            $totalApprovedVideos = $qcApprovedVideosCount + $atlasApprovedVideosCount;
            $totalApprovedMinutes = $qcApprovedMinutes + $atlasApprovedMinutes;
            $totalReportedVideos = $qcTotalVideosCount + $atlasTotalVideosCount;
            $totalReportedMinutes = $qcReportedMinutes + $atlasReportedMinutes;

            $reportedHours = round($totalReportedMinutes / 60, 1);
            $approvedHours = round($totalApprovedMinutes / 60, 1);

            $totalApprovedVideosAll += $totalApprovedVideos;
            $totalApprovedMinutesAll += $totalApprovedMinutes;

            // Check milestone and auto-create commission record if reached but not yet recorded
            $workerCommission = $commissions->firstWhere('worker_partner_id', $worker->id);
            if ($rekruter->partner_role === \App\Models\Partner::ROLE_REKRUTER && !$workerCommission && ($totalApprovedMinutes / 60) >= \App\Services\CheckRecruiterMilestone::MILESTONE_HOURS) {
                $workerCommission = \App\Models\RecruiterCommission::create([
                    'recruiter_partner_id' => $rekruter->id,
                    'worker_partner_id' => $worker->id,
                    'approved_hours_at_milestone' => round($totalApprovedMinutes / 60, 2),
                    'commission_amount' => \App\Services\CheckRecruiterMilestone::COMMISSION_AMOUNT,
                    'status' => 'pending',
                ]);
                $commissions->push($workerCommission);
            }

            $workersData[] = [
                'worker' => $worker,
                'reported_hours' => $reportedHours,
                'reported_hours_formatted' => $this->formatMinutes((int)$totalReportedMinutes),
                'reported_videos_count' => $totalReportedVideos,
                'approved_hours' => $approvedHours,
                'approved_hours_formatted' => $this->formatMinutes((int)$totalApprovedMinutes),
                'approved_videos_count' => $totalApprovedVideos,
                'approved_minutes' => (int)$totalApprovedMinutes,
                'recording_worked_minutes' => round($atlasReportedMinutes, 1),
                'recording_billable_minutes' => round($atlasApprovedMinutes, 1),
                'recording_pending_minutes' => round($atlasPendingMinutes, 1),
                'recording_rejected_minutes' => round($atlasRejectedMinutes, 1),
                'recording_worked_hours_formatted' => $this->formatMinutes($atlasReportedMinutes),
                'recording_billable_hours_formatted' => $this->formatMinutes($atlasApprovedMinutes),
                'recording_pending_hours_formatted' => $this->formatMinutes($atlasPendingMinutes),
                'recording_rejected_hours_formatted' => $this->formatMinutes($atlasRejectedMinutes),
                'milestone_reached' => $workerCommission !== null,
                'milestone_status' => $workerCommission?->status,
                'commission_amount' => $workerCommission ? (int)$workerCommission->commission_amount : \App\Services\CheckRecruiterMilestone::COMMISSION_AMOUNT,
                'commission' => $workerCommission,
            ];
        }

        $pendingCommissions = $commissions->where('status', 'pending');
        $paidCommissions = $commissions->where('status', 'paid');

        return [
            'recruited_workers_count' => $recruitedWorkers->count(),
            'total_approved_videos_count' => $totalApprovedVideosAll,
            'total_approved_minutes' => (int)$totalApprovedMinutesAll,
            'total_approved_hours_formatted' => $this->formatMinutes((int)$totalApprovedMinutesAll),
            'workers_data' => $workersData,
            'pending_commission_count' => $pendingCommissions->count(),
            'paid_commission_count' => $paidCommissions->count(),
            'pending_commission_amount' => $pendingCommissions->sum('commission_amount'),
            'paid_commission_amount' => $paidCommissions->sum('commission_amount'),
            'total_commission_amount' => $commissions->sum('commission_amount'),
            'claimable_workers' => collect($workersData)->filter(fn($w) => $w['milestone_reached'] && $w['milestone_status'] === 'pending')->values(),
        ];
    }

    /**
     * Get global metrics for Super Admin (cached 60s).
     */
    public function getGlobalMetrics(): array
    {
        return app(DashboardCacheService::class)->remember(
            DashboardCacheService::ownerGlobalKey(),
            DashboardCacheService::TTL_OWNER_GLOBAL,
            fn () => $this->computeGlobalMetrics()
        );
    }

    private function computeGlobalMetrics(): array
    {
        $totalWorkersCount = Partner::where('partner_role', 'worker')->count();
        $totalMitraCount = Partner::where('partner_role', 'mitra')->count();

        $allTime = (int) \App\Models\AtlasTask::sum('approved_minutes');
        $paid = (int) VideoWorkReport::where('qc_status', 'approved')->where('payment_status', 'paid')->sum('approved_duration_minutes');
        $pending = (int) VideoWorkReport::where('qc_status', 'approved')->where('payment_status', 'unpaid')->sum('approved_duration_minutes');

        $pendingMinutesSum = (int) VideoWorkReport::where('qc_status', 'pending')->sum('submitted_duration_minutes');
        $onReviewMinutesSum = (int) VideoWorkReport::where('qc_status', 'on_review')->sum('submitted_duration_minutes');
        $rejectedMinutesSum = (int) VideoWorkReport::where('qc_status', 'rejected')->sum('submitted_duration_minutes');

        $monthlyTargetHours = 8000;
        $today = now('Asia/Makassar');
        $todayDate = $today->toDateString();

        $daily = $this->computeTargetWindowMetrics(450, $todayDate, $todayDate, $today->format('d M Y'));
        $weeklyPeriod = $this->getWeeklyTargetRange($today);
        $weekly = $this->computeTargetWindowMetrics((int) round($monthlyTargetHours / 4), $weeklyPeriod['start']->toDateString(), $weeklyPeriod['end']->toDateString(), $weeklyPeriod['label']);
        $biweeklyPeriod = $this->getBiweeklyTargetRange($today);
        $biweekly = $this->computeTargetWindowMetrics((int) round($monthlyTargetHours / 2), $biweeklyPeriod['start']->toDateString(), $biweeklyPeriod['end']->toDateString(), $biweeklyPeriod['label']);
        $monthlyPeriod = $this->getMonthlyTargetRange($today);
        $monthly = $this->computeTargetWindowMetrics($monthlyTargetHours, $monthlyPeriod['start']->toDateString(), $monthlyPeriod['end']->toDateString(), $monthlyPeriod['label']);

        return [
            'total_workers' => $totalWorkersCount,
            'total_mitra' => $totalMitraCount,
            'global_all_time_minutes' => $allTime,
            'global_paid_minutes' => $paid,
            'global_pending_minutes' => $pending,
            'global_all_time_hours_formatted' => $this->formatMinutes($allTime),
            'global_paid_hours_formatted' => $this->formatMinutes($paid),
            'global_pending_hours_formatted' => $this->formatMinutes($pending),
            'global_pending_submitted_hours_formatted' => $this->formatMinutes($pendingMinutesSum),
            'global_on_review_submitted_hours_formatted' => $this->formatMinutes($onReviewMinutesSum),
            'global_rejected_submitted_hours_formatted' => $this->formatMinutes($rejectedMinutesSum),

            // Target Harian, Mingguan, Per 2 Minggu & Bulanan (disinkronkan dengan UTC+8 / WITA Recording)
            'daily_target_hours' => $daily['target_hours'],
            'daily_approved_minutes' => $daily['approved_minutes'],
            'daily_approved_hours' => $daily['approved_hours'],
            'daily_submitted_hours' => $daily['submitted_hours'],
            'daily_approved_hours_formatted' => $daily['approved_hours_formatted'],
            'daily_progress_percent' => $daily['progress_percent'],
            'daily_submitted_percent' => $daily['submitted_percent'],
            'daily_period_label' => $daily['period_label'],

            'weekly_target_hours' => $weekly['target_hours'],
            'weekly_approved_minutes' => $weekly['approved_minutes'],
            'weekly_approved_hours' => $weekly['approved_hours'],
            'weekly_submitted_hours' => $weekly['submitted_hours'],
            'weekly_approved_hours_formatted' => $weekly['approved_hours_formatted'],
            'weekly_progress_percent' => $weekly['progress_percent'],
            'weekly_submitted_percent' => $weekly['submitted_percent'],
            'weekly_period_label' => $weekly['period_label'],

            'biweekly_target_hours' => $biweekly['target_hours'],
            'biweekly_approved_minutes' => $biweekly['approved_minutes'],
            'biweekly_approved_hours' => $biweekly['approved_hours'],
            'biweekly_submitted_hours' => $biweekly['submitted_hours'],
            'biweekly_approved_hours_formatted' => $biweekly['approved_hours_formatted'],
            'biweekly_progress_percent' => $biweekly['progress_percent'],
            'biweekly_submitted_percent' => $biweekly['submitted_percent'],
            'biweekly_period_label' => $biweekly['period_label'],

            'monthly_target_hours' => $monthly['target_hours'],
            'monthly_approved_minutes' => $monthly['approved_minutes'],
            'monthly_approved_hours' => $monthly['approved_hours'],
            'monthly_submitted_hours' => $monthly['submitted_hours'],
            'monthly_approved_hours_formatted' => $monthly['approved_hours_formatted'],
            'monthly_progress_percent' => $monthly['progress_percent'],
            'monthly_submitted_percent' => $monthly['submitted_percent'],
            'monthly_period_label' => $monthly['period_label'],
        ];
    }

    /**
     * Aggregate AtlasTask approved & worked minutes within a WITA (Asia/Makassar) date window,
     * using the indexed task_date column (aligned to Asia/Makassar on ingest).
     */
    private function computeTargetWindowMetrics(int $targetHours, string $startDate, string $endDate, string $label): array
    {
        $totals = \App\Models\AtlasTask::whereBetween('task_date', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(approved_minutes), 0) as approved_sum, COALESCE(SUM(worked_minutes), 0) as worked_sum')
            ->first();

        $approvedMinutes = (int) round((float) ($totals->approved_sum ?? 0));
        $submittedMinutes = (int) round((float) ($totals->worked_sum ?? 0));
        $targetMinutes = $targetHours * 60;

        return [
            'target_hours' => $targetHours,
            'approved_minutes' => $approvedMinutes,
            'approved_hours' => round($approvedMinutes / 60, 1),
            'submitted_hours' => round($submittedMinutes / 60, 1),
            'approved_hours_formatted' => $this->formatMinutes($approvedMinutes),
            'progress_percent' => $targetMinutes > 0 ? round(($approvedMinutes / $targetMinutes) * 100, 1) : 0,
            'submitted_percent' => $targetMinutes > 0 ? round(($submittedMinutes / $targetMinutes) * 100, 1) : 0,
            'period_label' => $label,
        ];
    }

    /**
     * Get the rolling 14-day (bi-weekly) target window anchored to the 16th of the monthly cycle.
     * Days 0-13 map to the first 2 weeks (16th..16th+13); day 14+ maps to the remainder up to the 15th cutoff.
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function getBiweeklyTargetRange(?Carbon $referenceDate = null): array
    {
        $now = ($referenceDate ? $referenceDate->copy() : now('Asia/Makassar'))->startOfDay();
        $monthlyPeriod = $this->getMonthlyTargetRange($now);
        $monthlyStart = $monthlyPeriod['start'];
        $monthlyEnd = $monthlyPeriod['end'];

        $daysSinceStart = (int) $monthlyStart->diffInDays($now);
        if ($daysSinceStart < 14) {
            $start = $monthlyStart->copy()->startOfDay();
            $end = $start->copy()->addDays(13)->endOfDay();
            if ($end->greaterThan($monthlyEnd)) {
                $end = $monthlyEnd->copy();
            }
        } else {
            $start = $monthlyStart->copy()->addDays(14)->startOfDay();
            $end = $monthlyEnd->copy();
        }

        $label = $start->year === $end->year
            ? $start->format('d M') . ' - ' . $end->format('d M Y')
            : $start->format('d M Y') . ' - ' . $end->format('d M Y');

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
        ];
    }

    /**
     * Get the rolling 7-day weekly target window anchored to the 16th of the monthly cycle,
     * capped at the 15th cutoff so each cycle resets cleanly on the 16th.
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function getWeeklyTargetRange(?Carbon $referenceDate = null): array
    {
        $now = ($referenceDate ? $referenceDate->copy() : now('Asia/Makassar'))->startOfDay();
        $monthlyPeriod = $this->getMonthlyTargetRange($now);
        $monthlyStart = $monthlyPeriod['start'];
        $monthlyEnd = $monthlyPeriod['end'];

        $daysSinceStart = (int) $monthlyStart->diffInDays($now);
        $weekIndex = intdiv($daysSinceStart, 7);

        $start = $monthlyStart->copy()->addDays($weekIndex * 7)->startOfDay();
        $end = $start->copy()->addDays(6)->endOfDay();

        if ($end->greaterThan($monthlyEnd)) {
            $end = $monthlyEnd->copy();
        }

        $label = $start->year === $end->year
            ? $start->format('d M') . ' - ' . $end->format('d M Y')
            : $start->format('d M Y') . ' - ' . $end->format('d M Y');

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
        ];
    }

    /**
     * Get the rolling monthly target period (16th of current/previous month to 15th of next/current month).
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function getMonthlyTargetRange(?Carbon $referenceDate = null): array
    {
        $now = ($referenceDate ? $referenceDate->copy() : now('Asia/Makassar'))->startOfDay();

        if ($now->day >= 16) {
            $start = $now->copy()->startOfMonth()->day(16)->startOfDay();
            $end = $now->copy()->startOfMonth()->addMonthNoOverflow()->day(15)->endOfDay();
        } else {
            $start = $now->copy()->startOfMonth()->subMonthNoOverflow()->day(16)->startOfDay();
            $end = $now->copy()->startOfMonth()->day(15)->endOfDay();
        }

        $label = $start->year === $end->year
            ? $start->format('d M') . ' - ' . $end->format('d M Y')
            : $start->format('d M Y') . ' - ' . $end->format('d M Y');

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
        ];
    }

    /**
     * Helper to format minutes to a clean "Xh Ym" string.
     */
    public function formatMinutes(int|float $minutes): string
    {
        $minutes = (int) round((float) $minutes);
        $hours = floor($minutes / 60);
        $remaining = $minutes % 60;
        if ($hours > 0) {
            return "{$hours}j {$remaining}m";
        }
        return "{$remaining}m";
    }

    private function resolveAtlasWorkerIds(Partner $partner): \Illuminate\Support\Collection
    {
        if (! $partner->user_id && empty($partner->email)) {
            return collect();
        }

        return \App\Models\AtlasWorker::where(function ($q) use ($partner) {
            if ($partner->user_id) {
                $q->where('user_id', $partner->user_id);
            }
            if (! empty($partner->email)) {
                $partner->user_id
                    ? $q->orWhere('atlas_email', $partner->email)
                    : $q->where('atlas_email', $partner->email);
            }
        })->pluck('id');
    }

    /**
     * Batch-resolve AtlasWorker IDs and aggregate AtlasTask metrics for a collection of partners.
     *
     * @return array<string, array{task_count: int, approved_count: int, worked_minutes: float, approved_minutes: float, review_minutes: float, rejected_minutes: float, today_worked_minutes: float, highest_daily_minutes: float}>
     */
    private function buildBatchAtlasStats(\Illuminate\Support\Collection $partners, bool $includeDailyExtremes = false): array
    {
        if ($partners->isEmpty()) {
            return [];
        }

        $userIds = $partners->pluck('user_id')->filter()->unique()->values();
        $emails = $partners->pluck('email')->filter()->unique()->values();

        if ($userIds->isEmpty() && $emails->isEmpty()) {
            return [];
        }

        $atlasWorkers = \App\Models\AtlasWorker::query()
            ->where(function ($q) use ($userIds, $emails) {
                if ($userIds->isNotEmpty()) {
                    $q->whereIn('user_id', $userIds);
                }
                if ($emails->isNotEmpty()) {
                    $userIds->isNotEmpty()
                        ? $q->orWhereIn('atlas_email', $emails)
                        : $q->whereIn('atlas_email', $emails);
                }
            })
            ->get(['id', 'user_id', 'atlas_email']);

        $byUserId = $atlasWorkers->whereNotNull('user_id')->groupBy('user_id');
        $byEmail = $atlasWorkers->whereNotNull('atlas_email')->groupBy('atlas_email');

        $partnerAtlasIds = [];
        $allAtlasIds = collect();

        foreach ($partners as $partner) {
            $ids = collect();
            if ($partner->user_id && $byUserId->has($partner->user_id)) {
                $ids = $ids->merge($byUserId->get($partner->user_id)->pluck('id'));
            }
            if (! empty($partner->email) && $byEmail->has($partner->email)) {
                $ids = $ids->merge($byEmail->get($partner->email)->pluck('id'));
            }
            $uniqueIds = $ids->unique()->values();
            $partnerAtlasIds[$partner->id] = $uniqueIds;
            $allAtlasIds = $allAtlasIds->merge($uniqueIds);
        }

        $allAtlasIds = $allAtlasIds->unique()->values();
        if ($allAtlasIds->isEmpty()) {
            return [];
        }

        $totalsByAtlasWorker = \App\Models\AtlasTask::query()
            ->whereIn('atlas_worker_id', $allAtlasIds)
            ->groupBy('atlas_worker_id')
            ->selectRaw('atlas_worker_id, COUNT(*) as task_count, SUM(CASE WHEN approved_minutes > 0 THEN 1 ELSE 0 END) as approved_count, COALESCE(SUM(worked_minutes), 0) as worked_minutes, COALESCE(SUM(approved_minutes), 0) as approved_minutes, COALESCE(SUM(review_minutes), 0) as review_minutes, COALESCE(SUM(rejected_minutes), 0) as rejected_minutes')
            ->get()
            ->keyBy('atlas_worker_id');

        $todayByAtlasWorker = collect();
        $dailyByAtlasWorker = collect();

        if ($includeDailyExtremes) {
            $todayByAtlasWorker = \App\Models\AtlasTask::query()
                ->whereIn('atlas_worker_id', $allAtlasIds)
                ->whereDate('task_date', now())
                ->groupBy('atlas_worker_id')
                ->selectRaw('atlas_worker_id, COALESCE(SUM(worked_minutes), 0) as today_worked')
                ->pluck('today_worked', 'atlas_worker_id');

            $dailyByAtlasWorker = \App\Models\AtlasTask::query()
                ->whereIn('atlas_worker_id', $allAtlasIds)
                ->groupBy('atlas_worker_id', 'task_date')
                ->selectRaw('atlas_worker_id, task_date, SUM(worked_minutes) as daily_total')
                ->get()
                ->groupBy('atlas_worker_id');
        }

        $result = [];
        foreach ($partners as $partner) {
            $entry = $this->emptyAtlasBatchEntry();
            $dailyTotals = [];

            foreach ($partnerAtlasIds[$partner->id] as $awId) {
                $row = $totalsByAtlasWorker->get($awId);
                if ($row) {
                    $entry['task_count'] += (int) $row->task_count;
                    $entry['approved_count'] += (int) $row->approved_count;
                    $entry['worked_minutes'] += (float) $row->worked_minutes;
                    $entry['approved_minutes'] += (float) $row->approved_minutes;
                    $entry['review_minutes'] += (float) $row->review_minutes;
                    $entry['rejected_minutes'] += (float) $row->rejected_minutes;
                }

                if ($includeDailyExtremes) {
                    $entry['today_worked_minutes'] += (float) ($todayByAtlasWorker->get($awId) ?? 0);
                    foreach ($dailyByAtlasWorker->get($awId, collect()) as $dailyRow) {
                        $dateKey = (string) $dailyRow->task_date;
                        $dailyTotals[$dateKey] = ($dailyTotals[$dateKey] ?? 0.0) + (float) $dailyRow->daily_total;
                    }
                }
            }

            if ($includeDailyExtremes && ! empty($dailyTotals)) {
                $entry['highest_daily_minutes'] = (float) max($dailyTotals);
            }

            $result[$partner->id] = $entry;
        }

        return $result;
    }

    private function emptyAtlasBatchEntry(): array
    {
        return [
            'task_count' => 0,
            'approved_count' => 0,
            'worked_minutes' => 0.0,
            'approved_minutes' => 0.0,
            'review_minutes' => 0.0,
            'rejected_minutes' => 0.0,
            'today_worked_minutes' => 0.0,
            'highest_daily_minutes' => 360.0,
        ];
    }

    private function buildWorkerMetricsFromBatch(Partner $worker, \Illuminate\Support\Collection $approvedReports, array $atlasStats): array
    {
        $totalSubmittedMinutes = (float) $atlasStats['worked_minutes'];
        $allTimeMinutes = (float) $atlasStats['approved_minutes'];
        $recordingBillableMinutes = (float) $atlasStats['approved_minutes'];
        $recordingPendingMinutes = (float) $atlasStats['review_minutes'];
        $recordingRejectedMinutes = (float) $atlasStats['rejected_minutes'];
        $todaySubmittedMinutes = (float) $atlasStats['today_worked_minutes'];
        $highestDailyMinutes = (float) $atlasStats['highest_daily_minutes'];

        $paidMinutes = (float) $approvedReports->where('payment_status', 'paid')
            ->sum('approved_duration_minutes');

        $pendingMinutes = (float) $approvedReports->where('payment_status', 'unpaid')
            ->sum('approved_duration_minutes');

        $rate = $worker->effectiveHourlyRate();
        $paidEarnings = ($paidMinutes / 60) * $rate;
        $pendingEarnings = ($pendingMinutes / 60) * $rate;
        $totalEarnings = $paidEarnings + $pendingEarnings;

        return [
            'total_submitted_minutes' => round($totalSubmittedMinutes, 1),
            'today_submitted_minutes' => round($todaySubmittedMinutes, 1),
            'highest_daily_minutes' => round($highestDailyMinutes, 1),
            'all_time_minutes' => round($allTimeMinutes, 1),
            'paid_minutes' => round($paidMinutes, 1),
            'pending_minutes' => round($pendingMinutes, 1),
            'recording_worked_minutes' => round($totalSubmittedMinutes, 1),
            'recording_billable_minutes' => round($recordingBillableMinutes, 1),
            'recording_pending_minutes' => round($recordingPendingMinutes, 1),
            'recording_rejected_minutes' => round($recordingRejectedMinutes, 1),
            'total_submitted_hours_formatted' => $this->formatMinutes($totalSubmittedMinutes),
            'all_time_hours_formatted' => $this->formatMinutes($allTimeMinutes),
            'paid_hours_formatted' => $this->formatMinutes($paidMinutes),
            'pending_hours_formatted' => $this->formatMinutes($pendingMinutes),
            'recording_worked_hours_formatted' => $this->formatMinutes($totalSubmittedMinutes),
            'recording_billable_hours_formatted' => $this->formatMinutes($recordingBillableMinutes),
            'recording_pending_hours_formatted' => $this->formatMinutes($recordingPendingMinutes),
            'recording_rejected_hours_formatted' => $this->formatMinutes($recordingRejectedMinutes),
            'paid_earnings' => $paidEarnings,
            'pending_earnings' => $pendingEarnings,
            'total_earnings' => $totalEarnings,
            'hourly_rate' => $rate,
        ];
    }
}
