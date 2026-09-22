<?php

namespace App\Mcp\Tools;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Support\Facades\DB;

class QuickReconcileByEmailTool extends BaseTool
{
    public function getName(): string
    {
        return 'quick_reconcile_by_email';
    }

    public function getDescription(): string
    {
        return 'Lakukan quick reconcile untuk banyak email dengan alokasi FIFO berdasar approved_hours dan rejected_hours. Sisa duration akan tetap pending.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'data' => [
                    'type' => 'array', 
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => ['type' => 'string'],
                            'approved_hours' => ['type' => 'number'],
                            'rejected_hours' => ['type' => 'number']
                        ],
                        'required' => ['email', 'approved_hours', 'rejected_hours']
                    ],
                    'description' => 'Daftar data email, approved_hours, dan rejected_hours'
                ]
            ],
            'required' => ['data']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.write';
    }

    public function execute(array $args, array $client)
    {
        $data = $args['data'] ?? [];

        if (empty($data) || !is_array($data)) {
            throw new \Exception("Parameter data wajib diisi dan berupa array.");
        }

        $results = [];
        $totalProcessedEmails = 0;
        $globalApprovedMinutesProcessed = 0;
        $globalRejectedMinutesProcessed = 0;
        $globalReportsAffected = 0;

        foreach ($data as $item) {
            $email = $item['email'] ?? null;
            $approvedHours = isset($item['approved_hours']) ? (float)$item['approved_hours'] : null;
            $rejectedHours = isset($item['rejected_hours']) ? (float)$item['rejected_hours'] : null;

            if (!$email || $approvedHours === null || $rejectedHours === null) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Data tidak lengkap (butuh email, approved_hours, rejected_hours)'];
                continue;
            }

            $approvedMinutes = (int) round($approvedHours * 60);
            $rejectedMinutes = (int) round($rejectedHours * 60);

            $partner = Partner::where('email', $email)
                ->orWhereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                })->first();

            if (!$partner) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Mitra tidak ditemukan'];
                continue;
            }

            $reports = VideoWorkReport::where('partner_id', $partner->id)
                ->whereIn('qc_status', ['pending', 'on_review'])
                ->orderBy('submission_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($reports->isEmpty()) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Tidak ada laporan pending/on_review'];
                continue;
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

                    if ($remainingApproved > 0) {
                        $appDur = min($submitted, $remainingApproved);
                        $newStatus = 'approved';
                        $remainingApproved -= $appDur;
                        $updated = true;
                    }

                    $remainingInReport = $submitted - $appDur;
                    if ($remainingInReport > 0 && $remainingRejected > 0) {
                        $rejDur = min($remainingInReport, $remainingRejected);
                        $remainingRejected -= $rejDur;
                        if ($appDur == 0) {
                            $newStatus = 'rejected';
                        }
                        $updated = true;
                    }

                    if ($updated) {
                        $report->qc_status = $newStatus;
                        $report->approved_duration_minutes = $appDur;
                        $report->verified_by = 1; 
                        $report->verified_at = now();
                        
                        if ($newStatus === 'rejected') {
                            $report->verifier_notes = 'Reconciled rejection via MCP Quick Reconcile';
                        } else if ($rejDur > 0) {
                            $report->verifier_notes = 'Partially rejected (' . $rejDur . 'm) via MCP Quick Reconcile';
                        } else {
                            $report->verifier_notes = 'Approved via MCP Quick Reconcile';
                        }

                        $report->save();
                        $totalProcessed++;
                    }
                }
            });

            $processedApprovedMinutes = $approvedMinutes - $remainingApproved;
            $processedRejectedMinutes = $rejectedMinutes - $remainingRejected;

            \App\Services\ActivityLogger::log('report.quick_reconcile', "MCP Quick Reconcile untuk {$partner->full_name}. Approved: {$processedApprovedMinutes}m, Rejected: {$processedRejectedMinutes}m. Total diperbarui: {$totalProcessed}");

            $totalProcessedEmails++;
            $globalApprovedMinutesProcessed += $processedApprovedMinutes;
            $globalRejectedMinutesProcessed += $processedRejectedMinutes;
            $globalReportsAffected += $totalProcessed;

            $results[] = [
                'email' => $email,
                'status' => 'success',
                'approved_minutes_processed' => $processedApprovedMinutes,
                'rejected_minutes_processed' => $processedRejectedMinutes,
                'reports_affected' => $totalProcessed
            ];
        }

        return [
            'message' => "Selesai memproses quick reconcile.",
            'summary' => [
                'emails_processed' => $totalProcessedEmails,
                'total_approved_minutes' => $globalApprovedMinutesProcessed,
                'total_rejected_minutes' => $globalRejectedMinutesProcessed,
                'total_reports_affected' => $globalReportsAffected
            ],
            'details' => $results
        ];
    }
}
