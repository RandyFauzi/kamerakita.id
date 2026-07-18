<?php

namespace App\Console\Commands;

use App\Models\VideoWorkReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateEvidenceFilesToPrivateStorage extends Command
{
    protected $signature = 'evidence:migrate-to-private
        {--delete-public : Delete public copies after they are copied to private storage}';

    protected $description = 'Copy video report evidence files from legacy storage disks to the configured evidence disk.';

    public function handle(): int
    {
        $target = Storage::disk('evidence');
        $legacyDisks = ['local', 'public'];

        $paths = VideoWorkReport::query()
            ->select(['evidence_email_image_path', 'evidence_app_quality_image_path', 'payment_reference_proof_path'])
            ->get()
            ->flatMap(fn (VideoWorkReport $report) => [
                $report->evidence_email_image_path,
                $report->evidence_app_quality_image_path,
                $report->payment_reference_proof_path,
            ])
            ->filter()
            ->unique()
            ->values();

        $copied = 0;
        $missing = 0;
        $deleted = 0;

        foreach ($paths as $path) {
            if ($target->exists($path)) {
                continue;
            }

            $sourceDiskName = collect($legacyDisks)->first(
                fn (string $diskName) => Storage::disk($diskName)->exists($path)
            );

            if (! $sourceDiskName) {
                $missing++;
                $this->warn("Missing evidence file: {$path}");
                continue;
            }

            $source = Storage::disk($sourceDiskName);
            $target->put($path, $source->get($path));
            $copied++;

            if ($this->option('delete-public')) {
                $public = Storage::disk('public');
                if ($public->exists($path)) {
                    $public->delete($path);
                    $deleted++;
                }
            }
        }

        $this->info("Evidence migration complete. Copied: {$copied}. Missing: {$missing}. Deleted public copies: {$deleted}.");

        return self::SUCCESS;
    }
}
