<?php

namespace App\Http\Controllers;

use App\Services\EvidenceFileBackupService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ShowAvatarController extends Controller
{
    public function __invoke(string $path): Response
    {
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

            $contents = $backup->decodedContents();
            $mimeType = $backup->mime_type;
        }

        return response($contents, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
