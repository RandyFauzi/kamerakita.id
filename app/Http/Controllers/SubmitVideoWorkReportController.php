<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmitVideoWorkReportController extends Controller
{
    public function create()
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner || $partner->partner_role !== 'worker') {
            return redirect()->route('dashboard')->with('error', 'Hanya akun dengan profil Worker yang dapat mengakses halaman ini.');
        }

        return view('video-submissions.submit-report', compact('partner'));
    }

    public function store(Request $request)
    {
        $partner = Partner::where('user_id', Auth::id())->first();

        if (!$partner || $partner->partner_role !== 'worker') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'submission_date' => 'required|date|before_or_equal:today',
            'submitted_duration_minutes' => 'required|integer|min:1|max:1440',
            'evidence_email_image_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'evidence_app_quality_image_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'submission_date.required' => 'Tanggal pengiriman wajib diisi.',
            'submission_date.before_or_equal' => 'Tanggal pengiriman tidak boleh melebihi hari ini.',
            'submitted_duration_minutes.required' => 'Durasi menit wajib diisi.',
            'submitted_duration_minutes.min' => 'Durasi menit minimal adalah 1 menit.',
            'evidence_email_image_path.required' => 'Bukti gambar email wajib diunggah.',
            'evidence_email_image_path.image' => 'File bukti email harus berupa gambar.',
            'evidence_app_quality_image_path.required' => 'Bukti kualitas aplikasi wajib diunggah.',
            'evidence_app_quality_image_path.image' => 'File bukti kualitas aplikasi harus berupa gambar.',
        ]);

        $emailPath = $this->compressAndStoreImage($request->file('evidence_email_image_path'), 'evidences/email');
        $qualityPath = $this->compressAndStoreImage($request->file('evidence_app_quality_image_path'), 'evidences/app-quality');

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

        return redirect()->route('dashboard')->with('success', 'Laporan kerja video Anda berhasil dikirim dan sedang menunggu antrean QC!');
    }

    /**
     * Compress and save uploaded image as JPEG with 75% quality to save space.
     */
    private function compressAndStoreImage($file, string $folder): string
    {
        if (! function_exists('imagejpeg')) {
            return $file->store($folder, 'public');
        }

        $mime = $file->getClientMimeType();
        $originalPath = $file->getPathname();
        
        $filename = Str::uuid() . '.jpg';
        $relativePath = trim($folder, '/') . '/' . $filename;

        // Initialize GD image resource
        $image = null;
        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $image = @imagecreatefromjpeg($originalPath);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($originalPath);
        } elseif ($mime === 'image/gif') {
            $image = @imagecreatefromgif($originalPath);
        }

        if ($image) {
            ob_start();
            imagejpeg($image, null, 75);
            $compressedImage = ob_get_clean();
            imagedestroy($image);

            if ($compressedImage === false) {
                return $file->store($folder, 'public');
            }

            Storage::disk('public')->put($relativePath, $compressedImage);

            return $relativePath;
        }

        // Fallback to default storage method if GD fails
        return $file->store($folder, 'public');
    }
}
