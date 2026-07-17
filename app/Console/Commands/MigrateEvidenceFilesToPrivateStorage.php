<?php

namespace App\Console\Commands;

use App\Models\VideoWorkReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateEvidenceFilesToPrivateStorage extends Command
{
    protected $signature = 'evidence:migrate-to-private
        {--delete-public : Delete public copies after they are copied to private storage}';

    protected $description = 'Copy video report evidence files from public storage to private local storage.';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $paths = VideoWorkReport::query()
            ->select(['evidence_email_image_path', 'evidence_app_quality_image_path'])
            ->get()
            ->flatMap(fn (VideoWorkReport $report) => [
                $report->evidence_email_image_path,
                $report->evidence_app_quality_image_path,
            ])
            ->filter()
            ->unique()
            ->values();

        $copied = 0;
        $missing = 0;
        $deleted = 0;

        foreach ($paths as $path) {
            if ($private->exists($path)) {
                continue;
            }

            if (! $public->exists($path)) {
                $missing++;
                $this->warn("Missing evidence file: {$path}");
                continue;
            }

            $private->put($path, $public->get($path));
            $copied++;

            if ($this->option('delete-public')) {
                $public->delete($path);
                $deleted++;
            }
        }

        $this->info("Evidence migration complete. Copied: {$copied}. Missing: {$missing}. Deleted public copies: {$deleted}.");

        return self::SUCCESS;
    }
}
