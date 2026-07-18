<?php

namespace App\Console\Commands;

use App\Models\EvidenceFileBackup;
use App\Models\VideoWorkReport;
use App\Services\EvidenceFileBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupEvidenceFilesToDatabase extends Command
{
    protected $signature = 'evidence:backup-to-database';

    protected $description = 'Back up existing report evidence files to the database for deployment-safe recovery.';

    public function handle(EvidenceFileBackupService $backupService): int
    {
        $columns = [
            'evidence_email_image_path',
            'evidence_app_quality_image_path',
            'payment_reference_proof_path',
        ];

        $paths = VideoWorkReport::query()
            ->select($columns)
            ->get()
            ->flatMap(fn (VideoWorkReport $report) => collect($columns)->map(fn (string $column) => $report->{$column}))
            ->filter()
            ->unique()
            ->values();

        $backedUp = 0;
        $alreadyBackedUp = 0;
        $missing = 0;
        $failed = 0;

        foreach ($paths as $path) {
            if (EvidenceFileBackup::query()->where('path', $path)->exists()) {
                $alreadyBackedUp++;

                continue;
            }

            $exists = collect(['evidence', 'local', 'public'])
                ->contains(fn (string $diskName) => Storage::disk($diskName)->exists($path));

            if (! $exists) {
                $missing++;
                $this->warn("Cannot back up missing evidence: {$path}");

                continue;
            }

            try {
                $backupService->backup($path);
                $backedUp++;
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Failed to back up {$path}: {$exception->getMessage()}");
            }
        }

        $this->info(
            "Evidence database backup complete. New: {$backedUp}. Existing: {$alreadyBackedUp}. Missing: {$missing}. Failed: {$failed}."
        );

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
