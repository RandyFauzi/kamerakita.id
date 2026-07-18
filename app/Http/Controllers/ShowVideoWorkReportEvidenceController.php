<?php

namespace App\Http\Controllers;

use App\Models\VideoWorkReport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ShowVideoWorkReportEvidenceController extends Controller
{
    public function __invoke(VideoWorkReport $report, string $type): Response
    {
        $user = auth()->user();
        if (in_array($user->role, ['worker', 'mitra'])) {
            $partner = \App\Models\Partner::where('user_id', $user->id)->first();
            abort_unless($partner && $report->partner_id === $partner->id, 403, 'Akses ditolak.');
        }

        $path = match ($type) {
            'email' => $report->evidence_email_image_path,
            'app-quality' => $report->evidence_app_quality_image_path,
            'payment' => $report->payment_reference_proof_path,
            default => abort(404),
        };

        abort_if(blank($path), 404);

        $disk = Storage::disk('evidence');

        if (! $disk->exists($path)) {
            $disk = Storage::disk('local');
        }

        if (! $disk->exists($path)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($path), 404);

        return response($disk->get($path), 200, [
            'Content-Type' => $disk->mimeType($path) ?: 'image/jpeg',
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
