<?php

namespace App\Console\Commands;

use App\Models\EvidenceFileBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RestoreEvidenceFilesFromDatabase extends Command
{
    protected $signature = 'evidence:restore-from-database';

    protected $description = 'Restore missing evidence files from database backups to private storage.';

    public function handle(): int
    {
        $disk = Storage::disk('evidence');
        $restored = 0;
        $existing = 0;
        $failed = 0;

        foreach (EvidenceFileBackup::query()->cursor() as $backup) {
            if ($disk->exists($backup->path)) {
                $existing++;

                continue;
            }

            try {
                $stored = $disk->put($backup->path, $backup->contents);

                if (! $stored || ! $disk->exists($backup->path)) {
                    throw new \RuntimeException('File could not be verified after restoration.');
                }

                $restored++;
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Failed to restore {$backup->path}: {$exception->getMessage()}");
            }
        }

        $this->info("Evidence restore complete. Restored: {$restored}. Already on disk: {$existing}. Failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
