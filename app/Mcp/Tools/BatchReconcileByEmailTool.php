<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\VideoWorkReport;

class BatchReconcileByEmailTool extends BaseTool
{
    public function getName(): string
    {
        return 'batch_reconcile_by_email';
    }

    public function getDescription(): string
    {
        return 'Otomatis bagikan kuota menit yang disetujui secara proporsional ke semua video pending partner berdasarkan daftar email.';
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
                            'target_minutes' => ['type' => 'integer']
                        ],
                        'required' => ['email', 'target_minutes']
                    ],
                    'description' => 'Daftar email dan target menit'
                ],
                'start_date' => ['type' => 'string', 'description' => 'Opsional. YYYY-MM-DD'],
                'end_date' => ['type' => 'string', 'description' => 'Opsional. YYYY-MM-DD'],
                'project_name' => ['type' => 'string', 'description' => 'Opsional. Nama project, misal: ATLAS']
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
        $startDate = $args['start_date'] ?? null;
        $endDate = $args['end_date'] ?? null;
        $projectName = $args['project_name'] ?? null;

        if (empty($data) || !is_array($data)) {
            throw new \Exception("Parameter data wajib diisi dan berupa array.");
        }

        $results = [];
        $totalProcessedEmails = 0;
        $totalAllocatedMinutes = 0;

        foreach ($data as $item) {
            $email = $item['email'] ?? null;
            $targetMinutes = $item['target_minutes'] ?? 0;

            if (!$email || $targetMinutes <= 0) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Email kosong atau target_minutes <= 0'];
                continue;
            }

            $user = User::where('email', $email)->first();
            if (!$user || !$user->partner) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'User atau Partner tidak ditemukan'];
                continue;
            }

            $partnerId = $user->partner->id;

            $query = VideoWorkReport::where('partner_id', $partnerId)
                ->whereIn('qc_status', ['pending', 'on_review']);

            if ($startDate) {
                $query->whereDate('submission_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('submission_date', '<=', $endDate);
            }
            if ($projectName) {
                $query->where('project_name', $projectName);
            }

            $pendingReports = $query->get();

            if ($pendingReports->isEmpty()) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Tidak ada video pending di kriteria ini'];
                continue;
            }

            $totalSubmitted = $pendingReports->sum('submitted_duration_minutes');
            if ($totalSubmitted <= 0) {
                $results[] = ['email' => $email, 'status' => 'skipped', 'reason' => 'Total submitted duration = 0'];
                continue;
            }

            $remainingQuota = $targetMinutes;
            $allocatedForUser = 0;

            foreach ($pendingReports as $index => $report) {
                $submitted = $report->submitted_duration_minutes;
                $proportion = $submitted / $totalSubmitted;
                
                if ($index === $pendingReports->count() - 1) {
                    $allocated = $remainingQuota;
                } else {
                    $allocated = (int) round($targetMinutes * $proportion);
                    $remainingQuota -= $allocated;
                }

                $allocated = min($allocated, $submitted);
                $allocatedForUser += $allocated;

                $report->update([
                    'qc_status' => 'approved',
                    'approved_duration_minutes' => $allocated,
                    'verifier_notes' => '(MCP Bot) Batch Reconcile by Email',
                    'verified_at' => now(),
                ]);
            }

            $totalProcessedEmails++;
            $totalAllocatedMinutes += $allocatedForUser;
            $results[] = [
                'email' => $email, 
                'status' => 'success', 
                'allocated' => $allocatedForUser,
                'reports_affected' => $pendingReports->count()
            ];
        }

        return [
            'message' => "Selesai memproses batch reconcile.",
            'summary' => [
                'emails_processed' => $totalProcessedEmails,
                'total_allocated_minutes' => $totalAllocatedMinutes,
            ],
            'details' => $results
        ];
    }
}
