<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\Http;

class CalculatePartnerMetricsService
{
    private const DEFAULT_WORKER_HOURLY_RATE_IDR = 54000;
    private const MITRA_COMMISSION_HOURLY_RATE_IDR = 9000;
    private const DEFAULT_MITRA_OWN_HOURLY_RATE_IDR = 63000;
    private const CLIENT_BILLING_HOURLY_RATE_IDR = 90000;
    private const AGENCY_MARGIN_HOURLY_RATE_IDR = 27000;

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

        $rate = $worker->base_hourly_rate ?: self::DEFAULT_WORKER_HOURLY_RATE_IDR;
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
     * Get global metrics for Super Admin, including dynamic live USD conversions.
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

        $clientPaidAmount = ($paid / 60) * self::CLIENT_BILLING_HOURLY_RATE_IDR;
        $clientPendingAmount = ($pending / 60) * self::CLIENT_BILLING_HOURLY_RATE_IDR;
        $agencyNetMargin = ($allTime / 60) * self::AGENCY_MARGIN_HOURLY_RATE_IDR;

        // Fetch live USD to IDR rate
        $usdToIdrRate = cache()->remember('usd_to_idr_rate', 3600, function() {
            try {
                $response = Http::timeout(3)->get('https://open.er-api.com/v6/latest/USD');
                if ($response->successful()) {
                    return $response->json()['rates']['IDR'] ?? 16200;
                }
            } catch (\Exception $e) {
                // Fallback
            }
            return 16200;
        });

        // Convert amounts to USD dynamically for admin view
        $clientPaidUsd = $clientPaidAmount / $usdToIdrRate;
        $clientPendingUsd = $clientPendingAmount / $usdToIdrRate;
        $agencyNetMarginUsd = $agencyNetMargin / $usdToIdrRate;
        $clientBillingHourlyRateUsd = self::CLIENT_BILLING_HOURLY_RATE_IDR / $usdToIdrRate;
        $agencyMarginHourlyRateUsd = self::AGENCY_MARGIN_HOURLY_RATE_IDR / $usdToIdrRate;

        return [
            'total_workers' => $totalWorkersCount,
            'total_mitra' => $totalMitraCount,
            'global_all_time_minutes' => $allTime,
            'global_paid_minutes' => $paid,
            'global_pending_minutes' => $pending,
            'global_all_time_hours_formatted' => $this->formatMinutes($allTime),
            'global_paid_hours_formatted' => $this->formatMinutes($paid),
            'global_pending_hours_formatted' => $this->formatMinutes($pending),

            // Financial Projections (IDR)
            'client_paid_amount' => $clientPaidAmount,
            'client_pending_amount' => $clientPendingAmount,
            'agency_net_margin' => $agencyNetMargin,
            'client_billing_hourly_rate' => self::CLIENT_BILLING_HOURLY_RATE_IDR,
            'agency_margin_hourly_rate' => self::AGENCY_MARGIN_HOURLY_RATE_IDR,

            // Live USD rates
            'usd_to_idr_rate' => $usdToIdrRate,
            'client_paid_amount_usd' => $clientPaidUsd,
            'client_pending_amount_usd' => $clientPendingUsd,
            'agency_net_margin_usd' => $agencyNetMarginUsd,
            'client_billing_hourly_rate_usd' => $clientBillingHourlyRateUsd,
            'agency_margin_hourly_rate_usd' => $agencyMarginHourlyRateUsd,
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
