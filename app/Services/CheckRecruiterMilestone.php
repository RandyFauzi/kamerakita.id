<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\RecruiterCommission;

class CheckRecruiterMilestone
{
    /**
     * Milestone threshold in hours — when a recruited worker crosses this,
     * the Rekruter earns a one-time commission.
     */
    public const MILESTONE_HOURS = 20;

    /**
     * Commission amount in IDR (Rp 100.000).
     */
    public const COMMISSION_AMOUNT = 100000;

    /**
     * Check whether this worker has crossed the 20-hour milestone for the first time.
     * If yes AND they were recruited by a Rekruter, create a commission record.
     *
     * @param  Partner  $worker  The worker whose report was just approved.
     * @return RecruiterCommission|null  The created commission record, or null if not triggered.
     */
    public static function check(Partner $worker): ?RecruiterCommission
    {
        // Worker must have been recruited by someone
        if (empty($worker->recruiter_partner_id)) {
            return null;
        }

        // Only Rekruter (not Mitra) earns the milestone commission
        $recruiter = $worker->recruiter;
        if (!$recruiter || $recruiter->partner_role !== Partner::ROLE_REKRUTER) {
            return null;
        }

        // Check if commission already exists for this recruiter-worker pair (avoid duplicates)
        $alreadyEarned = RecruiterCommission::where('recruiter_partner_id', $recruiter->id)
            ->where('worker_partner_id', $worker->id)
            ->exists();

        if ($alreadyEarned) {
            return null;
        }

        // Calculate total approved hours for this worker (all time)
        $totalApprovedMinutes = $worker->videoWorkReports()
            ->where('qc_status', 'approved')
            ->sum('approved_duration_minutes');

        $totalApprovedHours = $totalApprovedMinutes / 60;

        // Create commission record only if milestone is reached
        if ($totalApprovedHours >= self::MILESTONE_HOURS) {
            return RecruiterCommission::create([
                'recruiter_partner_id' => $recruiter->id,
                'worker_partner_id' => $worker->id,
                'approved_hours_at_milestone' => round($totalApprovedHours, 2),
                'commission_amount' => self::COMMISSION_AMOUNT,
                'status' => 'pending',
            ]);
        }

        return null;
    }
}
