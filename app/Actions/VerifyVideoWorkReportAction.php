<?php

namespace App\Actions;

use App\Models\VideoWorkReport;

class VerifyVideoWorkReportAction
{
    /**
     * Execute the verification action on a video work report.
     */
    public function execute(VideoWorkReport $report, array $data, int $verifierId): string
    {
        $action = $data['action'];

        if ($action === 'start_review') {
            $report->update([
                'qc_status' => 'on_review',
                'verified_by' => $verifierId,
                'verified_at' => now(),
            ]);
            return "Laporan {$report->id} telah dipindahkan ke status ON REVIEW.";
        }

        if ($action === 'approve_full') {
            $report->update([
                'approved_duration_minutes' => $report->submitted_duration_minutes,
                'qc_status' => 'approved',
                'verified_by' => $verifierId,
                'verified_at' => now(),
            ]);
            return "Laporan {$report->id} disetujui penuh ({$report->submitted_duration_minutes} menit).";
        }

        if ($action === 'approve_partial') {
            $approvedMinutes = intval($data['approved_duration_minutes']);
            $report->update([
                'approved_duration_minutes' => $approvedMinutes,
                'qc_status' => 'approved',
                'verifier_notes' => $data['verifier_notes'] ?? null,
                'verified_by' => $verifierId,
                'verified_at' => now(),
            ]);
            return "Laporan {$report->id} disetujui sebagian ({$approvedMinutes} menit).";
        }

        if ($action === 'reject') {
            $report->update([
                'approved_duration_minutes' => 0,
                'qc_status' => 'rejected',
                'verifier_notes' => $data['verifier_notes'],
                'verified_by' => $verifierId,
                'verified_at' => now(),
            ]);
            return "Laporan {$report->id} ditolak dengan alasan: " . $data['verifier_notes'];
        }

        if ($action === 'revert') {
            if ($report->payment_status === 'paid') {
                throw new \Exception("Laporan yang sudah dibayar (Paid) tidak dapat dikembalikan statusnya. Silakan batalkan pembayaran terlebih dahulu.");
            }
            $report->update([
                'approved_duration_minutes' => 0,
                'qc_status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
            ]);
            return "Status laporan {$report->id} berhasil dikembalikan ke PENDING.";
        }

        throw new \InvalidArgumentException("Aksi verifikasi tidak valid.");
    }
}
