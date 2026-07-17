<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;

class CalculatePartnerMetricsService
{
    /**
     * Get dynamic metrics for a single Worker partner.
     */
    public function getWorkerMetrics(Partner $worker): array
    {
        $approvedReports = VideoWorkReport::query()
            ->where('partner_id', $worker->id)
            ->where('qc_status', 'approved')
            ->get();

        $allTimeMinutes = $approvedReports->sum('approved_duration_minutes');
        
        $paidMinutes = $approvedReports->where('payment_status', 'paid')
            ->sum('approved_duration_minutes');
            
        $pendingMinutes = $approvedReports->where('payment_status', 'unpaid')
            ->sum('approved_duration_minutes');

        // Worker rate is fixed at $3.00 USD per hour
        $rate = 3.00;
        $paidEarnings = ($paidMinutes / 60) * $rate;
        $pendingEarnings = ($pendingMinutes / 60) * $rate;
        $totalEarnings = $paidEarnings + $pendingEarnings;

        return [
            'all_time_minutes' => $allTimeMinutes,
            'paid_minutes' => $paidMinutes,
            'pending_minutes' => $pendingMinutes,
            'all_time_hours_formatted' => $this->formatMinutes($allTimeMinutes),
            'paid_hours_formatted' => $this->formatMinutes($paidMinutes),
            'pending_hours_formatted' => $this->formatMinutes($pendingMinutes),
            'paid_earnings' => $paidEarnings,
            'pending_earnings' => $pendingEarnings,
            'total_earnings' => $totalEarnings,
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
        
        // Passive commission from workers ($0.50 USD per hour)
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

            // Commission tracking: $0.50 per hour
            $commissionPaid += ($metrics['paid_minutes'] / 60) * 0.50;
            $commissionPending += ($metrics['pending_minutes'] / 60) * 0.50;
        }

        // Mitra's own recordings at $3.50 USD per hour (if they recorded any)
        $ownReports = VideoWorkReport::query()
            ->where('partner_id', $mitra->id)
            ->where('qc_status', 'approved')
            ->get();

        $ownAllTime = $ownReports->sum('approved_duration_minutes');
        $ownPaid = $ownReports->where('payment_status', 'paid')->sum('approved_duration_minutes');
        $ownPending = $ownReports->where('payment_status', 'unpaid')->sum('approved_duration_minutes');

        $personalPaidEarnings = ($ownPaid / 60) * 3.50;
        $personalPendingEarnings = ($ownPending / 60) * 3.50;

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
        ];
    }

    /**
     * Get global metrics for Super Admin.
     */
    public function getGlobalMetrics(): array
    {
        $allWorkers = Partner::where('partner_role', 'worker')->get();
        $totalWorkersCount = $allWorkers->count();
        $totalMitraCount = Partner::where('partner_role', 'mitra')->count();

        $approvedReports = VideoWorkReport::where('qc_status', 'approved')->get();

        $allTime = $approvedReports->sum('approved_duration_minutes');
        $paid = $approvedReports->where('payment_status', 'paid')->sum('approved_duration_minutes');
        $pending = $approvedReports->where('payment_status', 'unpaid')->sum('approved_duration_minutes');

        // Billed to client (Mytronlabs) at $5.00/hour
        $clientPaidAmount = ($paid / 60) * 5.00;
        $clientPendingAmount = ($pending / 60) * 5.00;

        // Worker share = $3.00/hour
        $workerShare = ($allTime / 60) * 3.00;
        
        // Mitra share = $0.50/hour for managed workers + $3.50/hour for own
        $workerReports = VideoWorkReport::whereHas('partner', function($q) {
            $q->where('partner_role', 'worker');
        })->where('qc_status', 'approved')->get();
        
        $mitraReports = VideoWorkReport::whereHas('partner', function($q) {
            $q->where('partner_role', 'mitra');
        })->where('qc_status', 'approved')->get();
        
        $mitraOwnMinutes = $mitraReports->sum('approved_duration_minutes');
        $workerApprovedMinutes = $workerReports->sum('approved_duration_minutes');

        $mitraShare = ($workerApprovedMinutes / 60) * 0.50 + ($mitraOwnMinutes / 60) * 3.50;

        // Agency Net Margin = Billed ($5) - Worker ($3) - Mitra ($0.5) = $1.50 per hour
        $agencyNetMargin = ($allTime / 60) * 1.50;

        return [
            'total_workers' => $totalWorkersCount,
            'total_mitra' => $totalMitraCount,
            'global_all_time_minutes' => $allTime,
            'global_paid_minutes' => $paid,
            'global_pending_minutes' => $pending,
            'global_all_time_hours_formatted' => $this->formatMinutes($allTime),
            'global_paid_hours_formatted' => $this->formatMinutes($paid),
            'global_pending_hours_formatted' => $this->formatMinutes($pending),

            // Financial Projections
            'client_paid_amount' => $clientPaidAmount,
            'client_pending_amount' => $clientPendingAmount,
            'agency_net_margin' => $agencyNetMargin,
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
