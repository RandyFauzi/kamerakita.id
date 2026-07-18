<?php

namespace App\Services;

use App\Models\EvidenceFileBackup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class EvidenceFileBackupService
{
    public function backup(string $path): void
    {
        $disk = collect(['evidence', 'local', 'public'])
            ->map(fn (string $name) => Storage::disk($name))
            ->first(fn ($candidate) => $candidate->exists($path));

        if (! $disk) {
            throw new RuntimeException("File evidence tidak ditemukan untuk dicadangkan: {$path}");
        }

        $contents = $disk->get($path);

        EvidenceFileBackup::query()->updateOrCreate(
            ['path' => $path],
            [
                'mime_type' => $disk->mimeType($path) ?: 'image/jpeg',
                'file_size' => strlen($contents),
                'contents' => $contents,
            ],
        );
    }

    public function recover(string $path): ?EvidenceFileBackup
    {
        $backup = EvidenceFileBackup::query()->where('path', $path)->first();

        if (! $backup) {
            return null;
        }

        try {
            $disk = Storage::disk('evidence');

            if (! $disk->exists($path)) {
                $disk->put($path, $backup->contents);
            }
        } catch (Throwable $exception) {
            Log::warning('Evidence served from database but could not be restored to disk.', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);
        }

        return $backup;
    }

    public function delete(string $path): void
    {
        EvidenceFileBackup::query()->where('path', $path)->delete();
    }
}
