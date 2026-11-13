<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\Http;

class CalculatePartnerMetricsService
{
    private const DEFAULT_WORKER_HOURLY_RATE_IDR = 60000;
    private const MITRA_COMMISSION_HOURLY_RATE_IDR = 10000;
    private const DEFAULT_MITRA_OWN_HOURLY_RATE_IDR = 60000;

    /**
     * Get dynamic metrics for a single Worker partner.
     */
    public function getWorkerMetrics(Partner $worker): array
    {
        $allReports = VideoWorkReport::query()
            ->where('partner_id', $worker->id)
            ->get();

        $approvedReports = $allReports->where('qc_status', 'approved');

        // Worker Metrics via AtlasTask
        // Assuming AtlasWorker is linked to the Partner's user_id or email
        $atlasWorkerIds = \App\Models\AtlasWorker::where(function ($q) use ($worker) {
            $q->where('user_id', $worker->user_id)
              ->orWhere('atlas_email', $worker->email);
        })->pluck('id');

        $totalSubmittedMinutes = (float) (\App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)->sum('worked_minutes') ?? 0);
        $allTimeMinutes = (float) (\App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)->sum('approved_minutes') ?? 0);
        $todaySubmittedMinutes = (float) (\App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)
            ->whereDate('task_date', now())
            ->sum('worked_minutes') ?? 0);
            
        $highestDailyMinutes = (float) (\App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds)
            ->selectRaw('SUM(worked_minutes) as daily_total')
            ->groupBy('task_date')
            ->get()
            ->max('daily_total') ?? 360); // Fallback to 6 hours (360 mins) if no data

        $paidMinutes = (float) $approvedReports->where('payment_status', 'paid')
            ->sum('approved_duration_minutes');
            
        $pendingMinutes = (float) $approvedReports->where('payment_status', 'unpaid')
            ->sum('approved_duration_minutes');

        $rate = $worker->base_hourly_rate ?: self::DEFAULT_WORKER_HOURLY_RATE_IDR;
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
            'total_submitted_hours_formatted' => $this->formatMinutes($totalSubmittedMinutes),
            'all_time_hours_formatted' => $this->formatMinutes($allTimeMinutes),
            'paid_hours_formatted' => $this->formatMinutes($paidMinutes),
            'pending_hours_formatted' => $this->formatMinutes($pendingMinutes),
            'paid_earnings' => $paidEarnings,
            'pending_earnings' => $pendingEarnings,
            'total_earnings' => $totalEarnings,
            'hourly_rate' => $rate,
        ];
    }

    /**
     * Get aggregated metrics for a Mitra managing multiple Workers.
     */
    public function getMitraMetrics(Partner $mitra): array
    {
        // Get all workers assigned to this Mitra
        $workers = Partner::query()
            ->where('mitra_parent_id', $mitra->id)
            ->where('partner_role', 'worker')
            ->with('user')
            ->get();

        $workersData = [];
        $totalAllTime = 0;
        $totalPaid = 0;
        $totalPending = 0;
        
        // Passive commission from workers in Rupiah per approved hour.
        $commissionPaid = 0;
        $commissionPending = 0;

        foreach ($workers as $worker) {
            $metrics = $this->getWorkerMetrics($worker);
            
            $workersData[] = [
                'worker' => $worker,
                'metrics' => $metrics,
            ];

            $totalAllTime += $metrics['all_time_minutes'];
            $totalPaid += $metrics['paid_minutes'];
            $totalPending += $metrics['pending_minutes'];

            $commissionPaid += ($metrics['paid_minutes'] / 60) * self::MITRA_COMMISSION_HOURLY_RATE_IDR;
            $commissionPending += ($metrics['pending_minutes'] / 60) * self::MITRA_COMMISSION_HOURLY_RATE_IDR;
        }

        // Mitra's own recordings use the configured partner rate, with a Rupiah fallback.
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

            // Mitra own earnings
            'personal_paid_earnings' => $personalPaidEarnings,
            'personal_pending_earnings' => $personalPendingEarnings,
            'personal_all_time_hours_formatted' => $this->formatMinutes($ownAllTime),

            // Commission earnings
            'commission_paid_earnings' => $commissionPaid,
            'commission_pending_earnings' => $commissionPending,
            'commission_hourly_rate' => self::MITRA_COMMISSION_HOURLY_RATE_IDR,
            'personal_hourly_rate' => $mitraOwnRate,
        ];
    }

    /**
     * Get metrics for a Rekruter — tracks recruited workers and milestone commissions.
     */
    public function getRekruterMetrics(Partner $rekruter): array
    {
        // Workers recruited via referral code
        $recruitedWorkers = \App\Models\Partner::where('recruiter_partner_id', $rekruter->id)
            ->with(['videoWorkReports'])
            ->get();

        // Commission records from recruiter_commissions table
        $commissions = \App\Models\RecruiterCommission::where('recruiter_partner_id', $rekruter->id)
            ->with('worker')
            ->get();

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

            // 2. AtlasTask counts & minutes (Atlas Worker recordings)
            $atlasWorkerIds = \App\Models\AtlasWorker::where(function ($q) use ($worker) {
                $q->where('user_id', $worker->user_id)
                  ->orWhere('atlas_email', $worker->email);
            })->pluck('id');

            $atlasTasksQuery = \App\Models\AtlasTask::whereIn('atlas_worker_id', $atlasWorkerIds);
            $atlasApprovedTasks = (clone $atlasTasksQuery)->where('approved_minutes', '>', 0);
            $atlasApprovedVideosCount = (clone $atlasApprovedTasks)->count();
            $atlasApprovedMinutes = (float) (clone $atlasApprovedTasks)->sum('approved_minutes');
            $atlasReportedMinutes = (float) (clone $atlasTasksQuery)->sum('worked_minutes');
            $atlasTotalVideosCount = (clone $atlasTasksQuery)->count();

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
     * Get global metrics for Super Admin, including dynamic live USD conversions.
     */
    public function getGlobalMetrics(): array
    {
        $totalWorkersCount = Partner::where('partner_role', 'worker')->count();
        $totalMitraCount = Partner::where('partner_role', 'mitra')->count();

        $allTime = (int) \App\Models\AtlasTask::sum('approved_minutes');
        $paid = (int) VideoWorkReport::where('qc_status', 'approved')->where('payment_status', 'paid')->sum('approved_duration_minutes');
        $pending = (int) VideoWorkReport::where('qc_status', 'approved')->where('payment_status', 'unpaid')->sum('approved_duration_minutes');

        $pendingMinutesSum = (int) VideoWorkReport::where('qc_status', 'pending')->sum('submitted_duration_minutes');
        $onReviewMinutesSum = (int) VideoWorkReport::where('qc_status', 'on_review')->sum('submitted_duration_minutes');
        $rejectedMinutesSum = (int) VideoWorkReport::where('qc_status', 'rejected')->sum('submitted_duration_minutes');

        // 1. Target Mingguan (100 Jam Video Approved)
        $currentPeriod = PeriodService::getPeriodRange(now());
        $weeklyPeriodStart = $currentPeriod['start']->copy()->startOfDay();
        $weeklyPeriodEnd = $currentPeriod['end']->copy()->endOfDay();

        $weeklyApprovedMinutes = \App\Models\AtlasTask::whereBetween('task_date', [
                $weeklyPeriodStart->toDateString(),
                $weeklyPeriodEnd->toDateString(),
            ])
            ->sum('approved_minutes');

        $weeklySubmittedMinutes = \App\Models\AtlasTask::whereBetween('task_date', [
                $weeklyPeriodStart->toDateString(),
                $weeklyPeriodEnd->toDateString(),
            ])
            ->sum('worked_minutes');

        $weeklyTargetHours = 625;
        $weeklyTargetMinutes = $weeklyTargetHours * 60;
        $weeklyApprovedHours = round($weeklyApprovedMinutes / 60, 1);
        $weeklySubmittedHours = round($weeklySubmittedMinutes / 60, 1);
        $weeklyProgressPercent = $weeklyTargetMinutes > 0 ? round(($weeklyApprovedMinutes / $weeklyTargetMinutes) * 100, 1) : 0;
        $weeklySubmittedPercent = $weeklyTargetMinutes > 0 ? round(($weeklySubmittedMinutes / $weeklyTargetMinutes) * 100, 1) : 0;
        $weeklyPeriodLabel = $weeklyPeriodStart->format('d M') . ' - ' . $weeklyPeriodEnd->format('d M Y');

        // 2. Target Bulanan (400 Jam Video Approved)
        $monthlyStart = now()->copy()->startOfMonth();
        $monthlyEnd = now()->copy()->endOfMonth();

        $monthlyApprovedMinutes = \App\Models\AtlasTask::whereBetween('task_date', [
                $monthlyStart->toDateString(),
                $monthlyEnd->toDateString(),
            ])
            ->sum('approved_minutes');

        $monthlySubmittedMinutes = \App\Models\AtlasTask::whereBetween('task_date', [
                $monthlyStart->toDateString(),
                $monthlyEnd->toDateString(),
            ])
            ->sum('worked_minutes');

        $monthlyTargetHours = 2500;
        $monthlyTargetMinutes = $monthlyTargetHours * 60;
        $monthlyApprovedHours = round($monthlyApprovedMinutes / 60, 1);
        $monthlySubmittedHours = round($monthlySubmittedMinutes / 60, 1);
        $monthlyProgressPercent = $monthlyTargetMinutes > 0 ? round(($monthlyApprovedMinutes / $monthlyTargetMinutes) * 100, 1) : 0;
        $monthlySubmittedPercent = $monthlyTargetMinutes > 0 ? round(($monthlySubmittedMinutes / $monthlyTargetMinutes) * 100, 1) : 0;
        $monthlyPeriodLabel = now()->translatedFormat('F Y');

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

            // Target Mingguan & Bulanan
            'weekly_target_hours' => $weeklyTargetHours,
            'weekly_approved_minutes' => $weeklyApprovedMinutes,
            'weekly_approved_hours' => $weeklyApprovedHours,
            'weekly_submitted_hours' => $weeklySubmittedHours,
            'weekly_approved_hours_formatted' => $this->formatMinutes($weeklyApprovedMinutes),
            'weekly_progress_percent' => $weeklyProgressPercent,
            'weekly_submitted_percent' => $weeklySubmittedPercent,
            'weekly_period_label' => $weeklyPeriodLabel,

            'monthly_target_hours' => $monthlyTargetHours,
            'monthly_approved_minutes' => $monthlyApprovedMinutes,
            'monthly_approved_hours' => $monthlyApprovedHours,
            'monthly_submitted_hours' => $monthlySubmittedHours,
            'monthly_approved_hours_formatted' => $this->formatMinutes($monthlyApprovedMinutes),
            'monthly_progress_percent' => $monthlyProgressPercent,
            'monthly_submitted_percent' => $monthlySubmittedPercent,
            'monthly_period_label' => $monthlyPeriodLabel,
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
}
