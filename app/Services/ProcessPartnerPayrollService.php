<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\DB;

class ProcessPartnerPayrollService
{
    /**
     * Process payroll for a specific partner.
     * Transitions all approved 'unpaid' work reports to 'paid'.
     */
    public function executeForPartner(Partner $partner): array
    {
        return DB::transaction(function () use ($partner) {
            $unpaidReports = VideoWorkReport::query()
                ->where('partner_id', $partner->id)
                ->where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->get();

            $totalApprovedMinutes = $unpaidReports->sum('approved_duration_minutes');
            $totalHours = $totalApprovedMinutes / 60;
            $hourlyRate = $partner->base_hourly_rate;
            $totalEarnings = round($totalHours * $hourlyRate);

            // Update all to paid
            VideoWorkReport::query()
                ->whereIn('id', $unpaidReports->pluck('id'))
                ->update(['payment_status' => 'paid']);

            return [
                'partner_id' => $partner->id,
                'full_name' => $partner->full_name,
                'mitra_id' => $partner->mitra_id,
                'processed_reports_count' => $unpaidReports->count(),
                'total_approved_minutes' => $totalApprovedMinutes,
                'total_hours' => $totalHours,
                'hourly_rate' => $hourlyRate,
                'total_earnings' => $totalEarnings,
            ];
        });
    }

    /**
     * Process payroll for all active partners with unpaid approved reports.
     */
    public function executeAll(): array
    {
        $partners = Partner::where('status', 'active')->get();
        $results = [];

        foreach ($partners as $partner) {
            $hasUnpaid = VideoWorkReport::query()
                ->where('partner_id', $partner->id)
                ->where('qc_status', 'approved')
                ->where('payment_status', 'unpaid')
                ->exists();

            if ($hasUnpaid) {
                $results[] = $this->executeForPartner($partner);
            }
        }

        return $results;
    }
}
