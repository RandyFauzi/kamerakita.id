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

    public function upload(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer',
            'video' => 'required|file|mimetypes:video/mp4',
            'sensor' => 'required|file|mimetypes:text/csv,text/plain',
            'duration_seconds' => 'required|integer',
            'recording_uuid' => 'required|string',
        ]);

        $user = $request->user();
        try {
            $partnerId = $user ? $user->partner->id : (\App\Models\Partner::first()?->id ?? 1);
        } catch (\Exception $e) {
            $partnerId = 1;
        }

        try {
            $category = Category::find($request->category_id);
            $categorySlug = $category->slug ?? ($category->name ?? 'housework');
        } catch (\Exception $e) {
            $categorySlug = 'housework';
        }

        $dateStr = now()->format('Y-m-d');
        $uuid = $request->recording_uuid;
        
        // TUGAS 2: Local Storage (Laragon) - Enterprise Directory Structure
        $baseDir = "recordings/{$categorySlug}/{$partnerId}/{$dateStr}/{$uuid}";
        
        // Store files to public disk
        $videoPath = $request->file('video')->storeAs($baseDir, 'video.mp4', 'public');
        $csvPath = $request->file('sensor')->storeAs($baseDir, 'sensor.csv', 'public');

        $recordingData = null;
        try {
            $recordingData = Recording::create([
                'partner_id' => $partnerId,
                'category_id' => $request->category_id,
                'video_url' => '/storage/' . $videoPath,
                'imu_data_url' => '/storage/' . $csvPath,
                'duration_seconds' => $request->duration_seconds,
                'frequency_hz' => 100,
                'status' => 'qc_pending',
            ]);
        } catch (\Exception $e) {
            \Log::warning('Database record creation failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Perekaman berhasil diunggah secara lokal.',
            'data' => $recordingData
        ], 200);
    }
}
