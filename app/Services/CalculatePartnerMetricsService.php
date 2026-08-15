<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\Http;

class CalculatePartnerMetricsService
{
    private const DEFAULT_WORKER_HOURLY_RATE_IDR = 50000;
    private const MITRA_COMMISSION_HOURLY_RATE_IDR = 9000;
    private const DEFAULT_MITRA_OWN_HOURLY_RATE_IDR = 63000;

    /**
     * Get dynamic metrics for a single Worker partner.
     */
    public function getWorkerMetrics(Partner $worker): array
    {
        $allReports = VideoWorkReport::query()
            ->where('partner_id', $worker->id)
            ->get();

        $approvedReports = $allReports->where('qc_status', 'approved');

        $totalSubmittedMinutes = $allReports->sum('submitted_duration_minutes');
        $allTimeMinutes = $approvedReports->sum('approved_duration_minutes');
        
        $todayReports = $allReports->filter(function ($report) {
            return $report->submission_date ? $report->submission_date->isSameDay(now()) : false;
        });
        $todaySubmittedMinutes = $todayReports->sum('submitted_duration_minutes');
        
        $paidMinutes = $approvedReports->where('payment_status', 'paid')
            ->sum('approved_duration_minutes');
            
        $pendingMinutes = $approvedReports->where('payment_status', 'unpaid')
            ->sum('approved_duration_minutes');

        $rate = $worker->base_hourly_rate ?: self::DEFAULT_WORKER_HOURLY_RATE_IDR;
        $paidEarnings = ($paidMinutes / 60) * $rate;
        $pendingEarnings = ($pendingMinutes / 60) * $rate;
        $totalEarnings = $paidEarnings + $pendingEarnings;

        return [
            'total_submitted_minutes' => $totalSubmittedMinutes,
            'today_submitted_minutes' => $todaySubmittedMinutes,
            'all_time_minutes' => $allTimeMinutes,
            'paid_minutes' => $paidMinutes,
            'pending_minutes' => $pendingMinutes,
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
            ->with(['videoWorkReports' => fn($q) => $q->where('qc_status', 'approved')])
            ->get();

        // Commission records from recruiter_commissions table
        $commissions = \App\Models\RecruiterCommission::where('recruiter_partner_id', $rekruter->id)
            ->with('worker')
            ->get();

        $pendingCommissions = $commissions->where('status', 'pending');
        $paidCommissions = $commissions->where('status', 'paid');

        $workersData = [];
        foreach ($recruitedWorkers as $worker) {
            $approvedMinutes = $worker->videoWorkReports->sum('approved_duration_minutes');
            $approvedHours = round($approvedMinutes / 60, 1);
            $workerCommission = $commissions->firstWhere('worker_partner_id', $worker->id);

            $workersData[] = [
                'worker' => $worker,
                'approved_hours' => $approvedHours,
                'milestone_reached' => $workerCommission !== null,
                'milestone_status' => $workerCommission?->status,
            ];
        }

        return [
            'recruited_workers_count' => $recruitedWorkers->count(),
            'workers_data' => $workersData,
            'pending_commission_count' => $pendingCommissions->count(),
            'paid_commission_count' => $paidCommissions->count(),
            'pending_commission_amount' => $pendingCommissions->sum('commission_amount'),
            'paid_commission_amount' => $paidCommissions->sum('commission_amount'),
            'total_commission_amount' => $commissions->sum('commission_amount'),
        ];
    }

    /**
     * Get global metrics for Super Admin, including dynamic live USD conversions.
     */
    public function getGlobalMetrics(): array
    {
        $allWorkers = Partner::where('partner_role', 'worker')->get();
        $totalWorkersCount = $allWorkers->count();
        $totalMitraCount = Partner::where('partner_role', 'mitra')->count();

        $approvedReports = VideoWorkReport::with('partner')->where('qc_status', 'approved')->get();

        $allTime = $approvedReports->sum('approved_duration_minutes');
        $paid = $approvedReports->where('payment_status', 'paid')->sum('approved_duration_minutes');
        $pending = $approvedReports->where('payment_status', 'unpaid')->sum('approved_duration_minutes');

        $pendingMinutesSum = VideoWorkReport::where('qc_status', 'pending')->sum('submitted_duration_minutes');
        $onReviewMinutesSum = VideoWorkReport::where('qc_status', 'on_review')->sum('submitted_duration_minutes');
        $rejectedMinutesSum = VideoWorkReport::where('qc_status', 'rejected')->sum('submitted_duration_minutes');

        // 1. Target Mingguan (100 Jam Video Approved)
        $currentPeriod = PeriodService::getPeriodRange(now());
        $weeklyPeriodStart = $currentPeriod['start']->copy()->startOfDay();
        $weeklyPeriodEnd = $currentPeriod['end']->copy()->endOfDay();

        $weeklyApprovedMinutes = VideoWorkReport::where('qc_status', 'approved')
            ->whereBetween('submission_date', [
                $weeklyPeriodStart->toDateString(),
                $weeklyPeriodEnd->toDateString(),
            ])
            ->sum('approved_duration_minutes');

        $weeklyTargetHours = 100;
        $weeklyTargetMinutes = $weeklyTargetHours * 60;
        $weeklyApprovedHours = round($weeklyApprovedMinutes / 60, 1);
        $weeklyProgressPercent = $weeklyTargetMinutes > 0 ? round(($weeklyApprovedMinutes / $weeklyTargetMinutes) * 100, 1) : 0;
        $weeklyPeriodLabel = $weeklyPeriodStart->format('d M') . ' - ' . $weeklyPeriodEnd->format('d M Y');

        // 2. Target Bulanan (400 Jam Video Approved)
        $monthlyStart = now()->copy()->startOfMonth();
        $monthlyEnd = now()->copy()->endOfMonth();

        $monthlyApprovedMinutes = VideoWorkReport::where('qc_status', 'approved')
            ->whereBetween('submission_date', [
                $monthlyStart->toDateString(),
                $monthlyEnd->toDateString(),
            ])
            ->sum('approved_duration_minutes');

        $monthlyTargetHours = 400;
        $monthlyTargetMinutes = $monthlyTargetHours * 60;
        $monthlyApprovedHours = round($monthlyApprovedMinutes / 60, 1);
        $monthlyProgressPercent = $monthlyTargetMinutes > 0 ? round(($monthlyApprovedMinutes / $monthlyTargetMinutes) * 100, 1) : 0;
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
            'weekly_approved_hours_formatted' => $this->formatMinutes($weeklyApprovedMinutes),
            'weekly_progress_percent' => $weeklyProgressPercent,
            'weekly_period_label' => $weeklyPeriodLabel,

            'monthly_target_hours' => $monthlyTargetHours,
            'monthly_approved_minutes' => $monthlyApprovedMinutes,
            'monthly_approved_hours' => $monthlyApprovedHours,
            'monthly_approved_hours_formatted' => $this->formatMinutes($monthlyApprovedMinutes),
            'monthly_progress_percent' => $monthlyProgressPercent,
            'monthly_period_label' => $monthlyPeriodLabel,
        ];
    }

    /**
     * Helper to format minutes to a clean "Xh Ym" string.
     */
    private function formatMinutes(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $remaining = $minutes % 60;
        if ($hours > 0) {
            return "{$hours}j {$remaining}m";
        }
        return "{$remaining}m";
    }
}
