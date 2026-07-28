<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\EvidenceFileBackupService;
use App\Services\StoreEvidenceImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EditRejectedVideoWorkReportController extends Controller
{
    public function edit(VideoWorkReport $report)
    {
        $partner = $this->authorizedWorkerFor($report);

        return view('video-submissions.edit-rejected-report', [
            'partner' => $partner,
            'report' => $report,
        ]);
    }

    public function update(Request $request, VideoWorkReport $report)
    {
        $this->authorizedWorkerFor($report);

        $validated = $request->validate([
            'submission_date' => 'required|date|before_or_equal:today',
            'submitted_duration_minutes' => 'required|integer|min:1|max:1440',
            'evidence_email_image_path' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'evidence_app_quality_image_path' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'submission_date.required' => 'Tanggal pengiriman wajib diisi.',
            'submission_date.before_or_equal' => 'Tanggal pengiriman tidak boleh melebihi hari ini.',
            'submitted_duration_minutes.required' => 'Durasi menit wajib diisi.',
            'submitted_duration_minutes.min' => 'Durasi menit minimal adalah 1 menit.',
            'evidence_email_image_path.required' => 'Screenshot total durasi di aplikasi wajib diunggah ulang.',
            'evidence_email_image_path.image' => 'File screenshot total durasi harus berupa gambar.',
            'evidence_app_quality_image_path.required' => 'Screenshot bagian kualitas di aplikasi wajib diunggah ulang.',
            'evidence_app_quality_image_path.image' => 'File screenshot kualitas aplikasi harus berupa gambar.',
        ]);

        $oldPaths = [
            $report->evidence_email_image_path,
            $report->evidence_app_quality_image_path,
        ];
        $newEmailPath = null;
        $newQualityPath = null;

        try {
            $imageStorage = app(StoreEvidenceImageService::class);
            $newEmailPath = $imageStorage->store($request->file('evidence_email_image_path'), 'evidences/email');
            $newQualityPath = $imageStorage->store($request->file('evidence_app_quality_image_path'), 'evidences/app-quality');

            DB::transaction(function () use ($report, $validated, $newEmailPath, $newQualityPath): void {
                $report->update([
                    'submission_date' => $validated['submission_date'],
                    'submitted_duration_minutes' => $validated['submitted_duration_minutes'],
                    'evidence_email_image_path' => $newEmailPath,
                    'evidence_app_quality_image_path' => $newQualityPath,
                    'approved_duration_minutes' => 0,
                    'qc_status' => 'pending',
                    'payment_status' => 'unpaid',
                    'verifier_notes' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ]);

                $backup = app(EvidenceFileBackupService::class);
                $backup->backup($newEmailPath);
                $backup->backup($newQualityPath);
            });

            \App\Services\ActivityLogger::log('report.revise', "Merevisi laporan video ID {$report->id} tanggal {$validated['submission_date']} dengan durasi {$validated['submitted_duration_minutes']} menit.");
        } catch (Throwable $exception) {
            $this->deleteEvidenceFiles([$newEmailPath, $newQualityPath], true);

            Log::error('Failed to resubmit rejected video work report.', [
                'report_id' => $report->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Laporan gagal dikirim ulang karena file bukti tidak berhasil disimpan. Cek permission storage lalu coba lagi.');
        }

        $this->deleteEvidenceFiles($oldPaths, true);

        return redirect()
            ->route('video-submissions.report-history')
            ->with('success', 'Laporan berhasil diperbaiki dan masuk kembali ke antrean QC.');
    }

    private function authorizedWorkerFor(VideoWorkReport $report): Partner
    {
        $partner = Partner::query()
            ->where('user_id', Auth::id())
            ->where('partner_role', 'worker')
            ->firstOrFail();

        abort_unless($report->partner_id === $partner->id, 403);
        abort_unless($report->qc_status === 'rejected', 403, 'Hanya laporan yang ditolak yang bisa diperbaiki.');
        abort_unless($report->payment_status === 'unpaid', 403, 'Laporan yang sudah dibayar tidak bisa diperbaiki.');

        return $partner;
    }

    private function deleteEvidenceFiles(array $paths, bool $deleteBackup = false): void
    {
        foreach ($paths as $path) {
            if (! $path) {
                continue;
            }

            foreach (['evidence', 'local', 'public'] as $diskName) {
                try {
                    $disk = Storage::disk($diskName);

                    if ($disk->exists($path)) {
                        $disk->delete($path);
                    }
                } catch (Throwable $exception) {
                    Log::warning('Failed to clean up an evidence file.', [
                        'path' => $path,
                        'disk' => $diskName,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            if ($deleteBackup) {
                try {
                    app(EvidenceFileBackupService::class)->delete($path);
                } catch (Throwable $exception) {
                    Log::warning('Failed to clean up an evidence database backup.', [
                        'path' => $path,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }
    }
}
