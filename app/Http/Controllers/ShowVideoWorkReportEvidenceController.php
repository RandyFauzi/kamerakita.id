<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\VideoWorkReport;
use App\Services\EvidenceFileBackupService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ShowVideoWorkReportEvidenceController extends Controller
{
    public function __invoke(VideoWorkReport $report, string $type): Response
    {
        $user = auth()->user();
        if (in_array($user->role, ['worker', 'mitra'])) {
            $partner = Partner::where('user_id', $user->id)->first();
            abort_unless($partner && $report->partner_id === $partner->id, 403, 'Akses ditolak.');
        }

        $path = match ($type) {
            'email' => $report->evidence_email_image_path,
            'app-quality' => $report->evidence_app_quality_image_path,
            'payment' => $report->payment_reference_proof_path,
            default => abort(404),
        };

        abort_if(blank($path), 404);

        $disk = collect(['evidence', 'local', 'public'])
            ->map(fn (string $name) => Storage::disk($name))
            ->first(fn ($candidate) => $candidate->exists($path));

        if ($disk) {
            $contents = $disk->get($path);
            $mimeType = $disk->mimeType($path) ?: 'image/jpeg';
        } else {
            $backup = app(EvidenceFileBackupService::class)->recover($path);
            abort_unless($backup, 404);

            $contents = $backup->contents;
            $mimeType = $backup->mime_type;
        }

        return response($contents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
