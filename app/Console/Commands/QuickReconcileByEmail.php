<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\DB;

class QuickReconcileByEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qc:quick-reconcile {email} {approved_hours} {rejected_hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform quick reconcile for a partner by email based on hours (FIFO)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $approvedHours = (float) $this->argument('approved_hours');
        $rejectedHours = (float) $this->argument('rejected_hours');

        $approvedMinutes = (int) round($approvedHours * 60);
        $rejectedMinutes = (int) round($rejectedHours * 60);

        // Find partner
        $partner = Partner::where('email', $email)
            ->orWhereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })->first();

        if (!$partner) {
            $this->error("Mitra dengan email '{$email}' tidak ditemukan.");
            return 1;
        }

        $reports = VideoWorkReport::where('partner_id', $partner->id)
            ->whereIn('qc_status', ['pending', 'on_review'])
            ->orderBy('submission_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($reports->isEmpty()) {
            $this->warn("Tidak ada laporan pending/on_review untuk '{$email}'.");
            return 0;
        }

        $remainingApproved = $approvedMinutes;
        $remainingRejected = $rejectedMinutes;
        $totalProcessed = 0;

        DB::transaction(function () use ($reports, &$remainingApproved, &$remainingRejected, &$totalProcessed) {
            foreach ($reports as $report) {
                if ($remainingApproved <= 0 && $remainingRejected <= 0) {
                    break;
                }

                $submitted = $report->submitted_duration_minutes;
                $updated = false;
                $newStatus = $report->qc_status;
                $appDur = 0;
                $rejDur = 0;

                // Priority 1: Allocate to Approved
                if ($remainingApproved > 0) {
                    $appDur = min($submitted, $remainingApproved);
                    $newStatus = 'approved';
                    $remainingApproved -= $appDur;
                    $updated = true;
                }

                // If still some duration left in the report, allocate to Rejected
                $remainingInReport = $submitted - $appDur;
                if ($remainingInReport > 0 && $remainingRejected > 0) {
                    $rejDur = min($remainingInReport, $remainingRejected);
                    $remainingRejected -= $rejDur;
                    // If it wasn't approved at all, mark it as rejected
                    if ($appDur == 0) {
                        $newStatus = 'rejected';
                    }
                    $updated = true;
                }

                if ($updated) {
                    $report->qc_status = $newStatus;
                    $report->approved_duration_minutes = $appDur;
                    // Usually this is done by an admin, we'll assign system or first superadmin as verifier if needed
                    // For CLI, we can just leave it as null or 1 (if 1 is superadmin)
                    $report->verified_by = 1; 
                    $report->verified_at = now();
                    
                    if ($newStatus === 'rejected') {
                        $report->verifier_notes = 'Reconciled rejection via CLI Quick Reconcile';
                    } else if ($rejDur > 0) {
                        $report->verifier_notes = 'Partially rejected (' . $rejDur . 'm) via CLI Quick Reconcile';
                    } else {
                        $report->verifier_notes = 'Approved via CLI Quick Reconcile';
                    }

                    $report->save();
                    $totalProcessed++;
                }
            }
        });

        $processedApprovedHours = round(($approvedMinutes - $remainingApproved) / 60, 2);
        $processedRejectedHours = round(($rejectedMinutes - $remainingRejected) / 60, 2);

        \App\Services\ActivityLogger::log('report.quick_reconcile', "CLI Quick Reconcile untuk {$partner->full_name}. Approved: {$processedApprovedHours} jam, Rejected: {$processedRejectedHours} jam. Total laporan diperbarui: {$totalProcessed}");

        $this->info("Quick Reconcile berhasil untuk {$email}!");
        $this->info("Diproses: {$processedApprovedHours}j Approved, {$processedRejectedHours}j Rejected.");
        $this->info("Total laporan diperbarui: {$totalProcessed}");

        return 0;
    }
}
