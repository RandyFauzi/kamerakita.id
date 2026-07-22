<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\EvidenceFileBackupService;
use App\Services\PartnerActivityStatusService;
use App\Services\StoreEvidenceImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SubmitVideoWorkReportController extends Controller
{
    public function create()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (! $partner || $partner->partner_role !== 'worker') {
            return redirect()->route('dashboard')->with('error', 'Hanya akun dengan profil Worker yang dapat mengakses halaman ini.');
        }

        return view('video-submissions.submit-report', compact('partner'));
    }

    public function store(Request $request)
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (! $partner || $partner->partner_role !== 'worker') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

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
            'evidence_email_image_path.required' => 'Screenshot total durasi di aplikasi wajib diunggah.',
            'evidence_email_image_path.image' => 'File screenshot total durasi harus berupa gambar.',
            'evidence_app_quality_image_path.required' => 'Screenshot bagian kualitas di aplikasi wajib diunggah.',
            'evidence_app_quality_image_path.image' => 'File screenshot kualitas aplikasi harus berupa gambar.',
        ]);

        $emailPath = null;
        $qualityPath = null;

        try {
            $imageStorage = app(StoreEvidenceImageService::class);
            $emailPath = $imageStorage->store($request->file('evidence_email_image_path'), 'evidences/email');
            $qualityPath = $imageStorage->store($request->file('evidence_app_quality_image_path'), 'evidences/app-quality');

            DB::transaction(function () use ($partner, $validated, $emailPath, $qualityPath): void {
                VideoWorkReport::create([
                    'partner_id' => $partner->id,
                    'submission_date' => $validated['submission_date'],
                    'evidence_email_image_path' => $emailPath,
                    'evidence_app_quality_image_path' => $qualityPath,
                    'submitted_duration_minutes' => $validated['submitted_duration_minutes'],
                    'approved_duration_minutes' => 0,
                    'qc_status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                $backup = app(EvidenceFileBackupService::class);
                $backup->backup($emailPath);
                $backup->backup($qualityPath);

                app(PartnerActivityStatusService::class)->markActiveAfterReport($partner);
            });
        } catch (Throwable $exception) {
            foreach ([$emailPath, $qualityPath] as $path) {
                try {
                    if ($path && Storage::disk('evidence')->exists($path)) {
                        Storage::disk('evidence')->delete($path);
                    }
                } catch (Throwable) {
                    // The original upload failure is the actionable error.
                }
            }

            Log::error('Failed to store video work report evidence.', [
                'partner_id' => $partner->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Laporan gagal dikirim karena file bukti tidak berhasil disimpan. Cek permission folder storage/app/private lalu coba lagi.');
        }

        return redirect()->route('dashboard')->with('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
    }
}
