<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\PartnerActivityStatusService;
use App\Services\StoreEvidenceImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SimpleReportUploadController extends Controller
{
    public function create()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner || !in_array(strtolower(trim($partner->partner_role)), ['worker', 'mitra', 'rekruter'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        return view('reports.simple-create', compact('partner'));
    }

    public function store(Request $request)
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        // --- DIAGNOSTIC LOGGING ---
        $fileDiagnostics = [];
        foreach ($_FILES as $fieldName => $fileData) {
            $names = (array) ($fileData['name'] ?? []);
            foreach ($names as $i => $name) {
                $fileDiagnostics[] = [
                    'field' => $fieldName,
                    'name' => is_array($fileData['name']) ? ($fileData['name'][$i] ?? null) : $fileData['name'],
                    'size' => is_array($fileData['size']) ? ($fileData['size'][$i] ?? null) : $fileData['size'],
                    'error' => is_array($fileData['error']) ? ($fileData['error'][$i] ?? null) : $fileData['error'],
                ];
            }
        }

        Log::info('ReportUpload attempt', [
            'partner_id' => $partner->id,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
            'user_agent' => $request->header('User-Agent'),
            'post_keys' => array_keys($_POST),
            'files_keys' => array_keys($_FILES),
            'file_diagnostics' => $fileDiagnostics,
            'empty_payload' => empty($_POST) && empty($_FILES),
            'input_all' => $request->except(['_token', 'password']),
            'raw_content_start' => substr(file_get_contents('php://input'), 0, 500)
        ]);
        // --------------------------

        // Jika payload kosong sama sekali (termasuk Content-Length 0 yang sering terjadi di iOS akibat file belum selesai diproses/jaringan terputus)
        if (empty($request->except(['_token', 'password']))) {
            return redirect()->away('https://randyfauzi.github.io/sagan-upload-aja/');
        }

        if (empty($request->all()) && (int) $request->server('CONTENT_LENGTH') > 0) {
            return redirect()->away('https://randyfauzi.github.io/sagan-upload-aja/');
        }

        // Extremely simple rules as requested
        $validated = $request->validate([
            'project_name' => 'required|in:atlas,minutes_data',
            'submission_date' => 'required|date',
            'submitted_duration_minutes' => 'required|integer|min:1',
            'evidence_email_image_path' => 'required|file|max:10240', // Any file, max 10MB
            'evidence_app_quality_image_path' => 'nullable|file|max:10240',
            'evidence_submitted_image_paths' => 'nullable|array',
            'evidence_submitted_image_paths.*' => 'nullable|file|max:10240',
        ], [
            'required' => 'Kolom :attribute wajib diisi.',
            'max' => 'Ukuran file :attribute maksimal 10MB.',
            'file' => 'File :attribute tidak valid.',
        ]);

        $emailPath = null;
        $qualityPath = null;
        $submittedPaths = [];

        try {
            $imageStorage = app(StoreEvidenceImageService::class);
            $emailPath = $imageStorage->store($request->file('evidence_email_image_path'), 'evidences/email');

            if ($request->hasFile('evidence_app_quality_image_path')) {
                $qualityPath = $imageStorage->store($request->file('evidence_app_quality_image_path'), 'evidences/quality');
            }

            if ($request->hasFile('evidence_submitted_image_paths')) {
                foreach ($request->file('evidence_submitted_image_paths') as $file) {
                    if ($file) {
                        $submittedPaths[] = $imageStorage->store($file, 'evidences/submitted');
                    }
                }
            }

            DB::transaction(function () use ($partner, $validated, $emailPath, $qualityPath, $submittedPaths): void {
                $report = VideoWorkReport::create([
                    'partner_id' => $partner->id,
                    'project_name' => $validated['project_name'],
                    'submission_date' => $validated['submission_date'],
                    'evidence_email_image_path' => $emailPath,
                    'evidence_app_quality_image_path' => $qualityPath,
                    'evidence_submitted_image_paths' => $submittedPaths,
                    'submitted_duration_minutes' => $validated['submitted_duration_minutes'],
                    'approved_duration_minutes' => 0,
                    'qc_status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                \App\Jobs\ProcessSubmittedReport::dispatch($report)->afterCommit();
                app(PartnerActivityStatusService::class)->markActiveAfterReport($partner);
            });

            \App\Services\ActivityLogger::log('report.submit', "Mengirimkan laporan harian baru untuk tanggal {$validated['submission_date']} dengan durasi {$validated['submitted_duration_minutes']} menit.");
        } catch (Throwable $exception) {
            // Rollback files
            $pathsToDelete = array_filter(array_merge([$emailPath, $qualityPath], $submittedPaths));
            foreach ($pathsToDelete as $path) {
                try {
                    if ($path && Storage::disk('evidence')->exists($path)) {
                        Storage::disk('evidence')->delete($path);
                    }
                } catch (Throwable $e) {}
            }

            Log::error('Upload failed.', ['message' => $exception->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Laporan gagal dikirim. Pastikan file valid.');
        }

        return back()->with('success', 'Upload Berhasil!');
    }
}
