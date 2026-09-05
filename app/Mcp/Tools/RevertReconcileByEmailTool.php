<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\VideoWorkReport;

class RevertReconcileByEmailTool extends BaseTool
{
    public function getName(): string
    {
        return 'revert_reconcile_by_email';
    }

    public function getDescription(): string
    {
        return 'Membatalkan persetujuan batch reconcile untuk email tertentu.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'email' => ['type' => 'string'],
                'project_name' => ['type' => 'string']
            ],
            'required' => ['email']
        ];
    }

    public function getRequiredPermission(): string
    {
        return 'mcp.write';
    }

    public function execute(array $args, array $client)
    {
        try {
            $email = $args['email'] ?? null;
            $projectName = $args['project_name'] ?? null;

            if (!$email) {
                return ['error' => 'Parameter email wajib diisi.'];
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                return ['error' => "User dengan email {$email} tidak ditemukan."];
            }
            
            if (!$user->partner) {
                return ['error' => "Partner untuk user {$email} tidak ditemukan."];
            }

            $partnerId = $user->partner->id;

            $query = VideoWorkReport::where('partner_id', $partnerId)
                ->where('qc_status', 'approved');

            if ($projectName) {
                $query->where('project_name', $projectName);
            }

            $reports = $query->get();
            $updatedCount = 0;

            foreach ($reports as $report) {
                $report->update([
                    'qc_status' => 'pending',
                    'approved_duration_minutes' => 0,
                    'verifier_notes' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                ]);
                $updatedCount++;
            }

            return [
                'status' => 'success',
                'message' => "Berhasil membatalkan persetujuan untuk {$updatedCount} laporan milik {$email}."
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
}
