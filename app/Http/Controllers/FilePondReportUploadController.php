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
use Illuminate\Support\Str;
use Throwable;

class FilePondReportUploadController extends Controller
{
    public function create()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner || !in_array(strtolower(trim($partner->partner_role)), ['worker', 'mitra', 'rekruter'])) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        return view('reports.filepond-create', compact('partner'));
    }

    /**
     * FilePond Asynchronous Upload Endpoint
     */
    public function processTemp(Request $request)
    {
        // Parameter name could be 'evidence_email_image_path', 'evidence_submitted_image_paths[]'
        // FilePond sends files one by one
        $fileKeys = array_keys($_FILES);
        if (empty($fileKeys)) {
            return response()->json(['error' => 'No file found'], 400);
        }

        $key = $fileKeys[0];
        
        // Handle array of files (e.g., if FilePond sends multiple under evidence_submitted_image_paths[])
        // Actually, FilePond usually sends one file per request even for multiple inputs, but uses the input name.
        $file = $request->file($key);
        if (is_array($file)) {
            $file = $file[0];
        }

        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Invalid file upload'], 400);
        }

        // Generate a random folder for this temp upload
        $folder = uniqid('tmp_') . '-' . Str::random(10);
        $filename = $file->getClientOriginalName();
        
        // Store in temporary disk space
        $path = $file->storeAs('filepond-temp/' . $folder, $filename, 'local');

        // Return the folder name as the unique server ID
        // FilePond will set this as the value of the hidden input
        return response((string) $folder, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * FilePond Revert Endpoint (When user clicks X on an uploaded file)
     */
    public function revertTemp(Request $request)
    {
        $folder = $request->getContent();
        if ($folder && preg_match('/^tmp_[a-zA-Z0-9\-]+$/', $folder)) {
            Storage::disk('local')->deleteDirectory('filepond-temp/' . $folder);
            return response('', 200);
        }
        return response('', 400);
    }

    /**
     * Process the final form submission containing the FilePond server IDs
     */
    public function store(Request $request)
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        Log::info('FilePond ReportUpload attempt', [
            'partner_id' => $partner->id,
            'input_all' => $request->except(['_token', 'password']),
        ]);

        $validated = $request->validate([
            'project_name' => 'required|in:atlas,minutes_data',
            'submission_date' => 'required|date',
            'submitted_duration_minutes' => 'required|integer|min:1',
            // FilePond fields are strings (the folder name) instead of files
            'evidence_email_image_path' => 'required|string',
            'evidence_app_quality_image_path' => 'nullable|string',
            'evidence_submitted_image_paths' => 'nullable', // Can be string or array of strings
        ], [
            'required' => 'Kolom :attribute wajib diisi.',
        ]);

        $emailPath = null;
        $qualityPath = null;
        $submittedPaths = [];

        try {
            // Helper to move file from temp to final destination
            $moveTempFile = function ($folderId, $finalDir) {
                if (empty($folderId) || !is_string($folderId)) return null;
                $files = Storage::disk('local')->files('filepond-temp/' . $folderId);
                if (empty($files)) return null;
                
                $tempPath = $files[0];
                $filename = basename($tempPath);
                
                // Final path format like StoreEvidenceImageService (e.g. evidences/email/Ymd_uniq.jpg)
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $finalPath = $finalDir . '/' . date('Y/m/d') . '_' . Str::random(10) . '.' . $extension;
                
                // Copy to evidence disk (could be S3 or public/storage depending on config)
                // Assuming StoreEvidenceImageService uses standard local disk or 'evidence' disk
                $content = Storage::disk('local')->get($tempPath);
                Storage::disk('evidence')->put($finalPath, $content);
                
                // Clean up temp folder
                Storage::disk('local')->deleteDirectory('filepond-temp/' . $folderId);
                
                return $finalPath;
            };

            $emailPath = $moveTempFile($request->input('evidence_email_image_path'), 'evidences/email');
            
            if (!$emailPath) {
                return back()->withInput()->with('error', 'Screenshot Bukti Durasi gagal diproses atau hilang.');
            }

            if ($request->filled('evidence_app_quality_image_path')) {
                $qualityPath = $moveTempFile($request->input('evidence_app_quality_image_path'), 'evidences/quality');
            }

            if ($request->filled('evidence_submitted_image_paths')) {
                // Could be an array of strings or a JSON string if FilePond sends it weirdly, but usually array
                $batchIds = (array) $request->input('evidence_submitted_image_paths');
                foreach ($batchIds as $folderId) {
                    // Sometimes FilePond sends empty strings in arrays
                    if ($folderId) {
                        $path = $moveTempFile($folderId, 'evidences/submitted');
                        if ($path) $submittedPaths[] = $path;
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
            Log::error('FilePond Upload failed.', ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);

            return back()
                ->withInput()
                ->with('error', 'Laporan gagal dikirim. Terjadi kesalahan saat memproses gambar.');
        }

        return back()->with('success', 'Upload Berhasil!');
    }
}
