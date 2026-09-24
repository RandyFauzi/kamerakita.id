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

class SubmitVideoWorkReportController extends Controller
{
    public function create()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (! $partner || !in_array(strtolower(trim($partner->partner_role)), ['worker', 'mitra', 'rekruter'])) {
            return redirect()->route('dashboard')->with('error', 'Hanya akun dengan profil Kontributor, Mitra, atau Rekruter yang dapat mengakses halaman ini.');
        }

        return view('video-submissions.submit-report', compact('partner'));
    }

    public function store(Request $request)
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (! $partner || !in_array(strtolower(trim($partner->partner_role)), ['worker', 'mitra', 'rekruter'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $requestId = \Illuminate\Support\Str::uuid()->toString();

        Log::info('DIAGNOSTIC_PHASE_10', [
            'diagnostic_id' => $requestId,
            'partner_id' => $partner->id ?? null,
            'CONTENT_LENGTH' => $request->server('CONTENT_LENGTH'),
            'post_keys' => array_keys($request->all()),
            'file_keys' => array_keys($request->allFiles()),
            'has_project_name' => $request->has('project_name'),
            'has_submission_date' => $request->has('submission_date'),
            'has_duration' => $request->has('submitted_duration_minutes'),
            'has_email_file' => $request->hasFile('evidence_email_image_path'),
            'atlas_files_count' => count($request->file('evidence_submitted_image_paths', [])),
            'raw_post_size' => strlen(file_get_contents('php://input')),
        ]);

        if (empty($request->all()) && (int) $request->server('CONTENT_LENGTH') > 0) {
            Log::warning('Payload dropped due to server limits', [
                'diagnostic_id' => $requestId,
                'partner_id' => $partner->id,
                'content_length' => $request->server('CONTENT_LENGTH')
            ]);
            
            if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim laporan: Total ukuran file yang diunggah terlalu besar. Harap perkecil/kompres ukuran screenshot Anda (Otomatis dikompres) lalu coba lagi.'
                ], 413);
            }
            return back()->with('error', 'Gagal mengirim laporan: Total ukuran file terlalu besar.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'project_name' => 'required|in:atlas,minutes_data',
            'submission_date' => 'required|date|before_or_equal:today',
            'submitted_duration_minutes' => 'required|integer|min:1|max:1440',
            'evidence_email_image_path' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:30720',
            'evidence_app_quality_image_path' => 'required_if:project_name,minutes_data|image|mimes:jpeg,png,jpg,gif,webp|max:30720',
            'evidence_submitted_image_paths' => 'required_if:project_name,atlas|array|min:1',
            'evidence_submitted_image_paths.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:30720',
        ], [
            'project_name.required' => 'Aplikasi wajib dipilih.',
            'project_name.in' => 'Pilihan aplikasi tidak valid.',
            'submission_date.required' => 'Tanggal pengiriman wajib diisi.',
            'submission_date.before_or_equal' => 'Tanggal pengiriman tidak boleh melebihi hari ini.',
            'submitted_duration_minutes.required' => 'Durasi menit wajib diisi.',
            'submitted_duration_minutes.min' => 'Durasi menit minimal adalah 1 menit.',
            'evidence_email_image_path.required' => 'Screenshot total durasi di aplikasi wajib diunggah.',
            'evidence_email_image_path.image' => 'File screenshot total durasi harus berupa gambar.',
            'evidence_app_quality_image_path.required_if' => 'Screenshot bagian kualitas wajib diunggah untuk Minutes Data.',
            'evidence_app_quality_image_path.image' => 'File screenshot kualitas harus berupa gambar.',
            'evidence_submitted_image_paths.required_if' => 'Screenshot bagian unggahan wajib diunggah minimal 1 gambar untuk Atlas.',
            'evidence_submitted_image_paths.*.image' => 'Setiap file screenshot unggahan harus berupa gambar.',
        ]);
        
        if ($validator->fails()) {
            Log::info('Report submission validation failed', [
                'diagnostic_id' => $requestId,
                'partner_id' => $partner->id,
                'errors' => $validator->errors()->toArray(),
                'content_length' => $request->server('CONTENT_LENGTH')
            ]);
            
            if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat kesalahan pada isian form Anda.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validated();

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
                    $submittedPaths[] = $imageStorage->store($file, 'evidences/submitted');
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

                // Background Processing Queue
                \App\Jobs\ProcessSubmittedReport::dispatch($report)->afterCommit();

                app(PartnerActivityStatusService::class)->markActiveAfterReport($partner);
            });

            \App\Services\ActivityLogger::log('report.submit', "Mengirimkan laporan harian baru untuk tanggal {$validated['submission_date']} dengan durasi {$validated['submitted_duration_minutes']} menit.");
        } catch (Throwable $exception) {
            $pathsToDelete = array_filter(array_merge([$emailPath, $qualityPath], $submittedPaths));
            foreach ($pathsToDelete as $path) {
                try {
                    if ($path && Storage::disk('evidence')->exists($path)) {
                        Storage::disk('evidence')->delete($path);
                    }
                } catch (Throwable) {
                    // Ignore cleanup errors
                }
            }

            Log::error('Failed to store video work report evidence.', [
                'diagnostic_id' => $requestId,
                'partner_id' => $partner->id,
                'message' => $exception->getMessage(),
                'class' => get_class($exception)
            ]);

            if ($request->expectsJson() || $request->has('_ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan gagal dikirim karena kesalahan sistem internal. Silakan coba lagi.'
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Laporan gagal dikirim karena kesalahan sistem internal. Silakan coba lagi.');
        }

        if ($request->expectsJson() || $request->has('_ajax')) {
            session()->flash('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
            return response()->json([
                'success' => true,
                'message' => 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!',
                'redirect' => route('dashboard')
            ], 200);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
    }
}

