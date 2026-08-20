<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MobileRecordingController extends Controller
{
    public function getCategories()
    {
        $categories = Category::select('id', 'name', 'description')->get();
        return response()->json(['data' => $categories]);
    }

    public function generateUploadUrl(Request $request)
    {
        $request->validate([
            'file_type' => 'required|in:video,imu',
            'file_extension' => 'required|string', // e.g., 'mp4', 'csv'
        ]);

        $partner = $request->user()->partner;
        $uniqueId = Str::uuid()->toString();
        $datePrefix = now()->format('Y/m/d');
        
        // Pola nama file: mobile_recordings/2026/08/20/partnerId_uuid_video.mp4
        $filePath = "mobile_recordings/{$datePrefix}/{$partner->id}_{$uniqueId}_{$request->file_type}.{$request->file_extension}";

        // Membuat Presigned URL S3 yang valid selama 15 Menit untuk client melakukan PUT Object
        $uploadUrl = Storage::disk('s3')->temporaryUrl(
            $filePath, 
            now()->addMinutes(15),
            ['ResponseMethod' => 'PUT'] // Konfigurasi khusus AWS untuk memastikan method upload
        );

        return response()->json([
            'upload_url' => $uploadUrl,
            'file_path' => $filePath // Client wajib menyimpan file_path ini untuk disubmit ke completeRecording
        ]);
    }

    public function completeRecording(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'video_url' => 'required|string', // file_path dari AWS R2
            'imu_data_url' => 'required|string', // file_path dari AWS R2
            'duration_seconds' => 'required|integer|min:1',
            'frequency_hz' => 'nullable|integer',
        ]);

        $recording = Recording::create([
            'partner_id' => $request->user()->partner->id,
            'category_id' => $request->category_id,
            'video_url' => $request->video_url,
            'imu_data_url' => $request->imu_data_url,
            'duration_seconds' => $request->duration_seconds,
            'frequency_hz' => $request->frequency_hz ?? 100,
            'status' => 'qc_pending', // Langsung masuk status antrian QC
        ]);

        return response()->json([
            'message' => 'Perekaman berhasil diselesaikan dan masuk antrean QC.',
            'data' => $recording
        ], 201);
    }
}
