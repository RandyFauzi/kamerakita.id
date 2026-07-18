<?php

namespace App\Console\Commands;

use App\Models\VideoWorkReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckEvidenceFiles extends Command
{
    protected $signature = 'evidence:check-files {--show-missing : Show every missing database path}';

    protected $description = 'Check whether report evidence paths stored in the database still exist on disk.';

    public function handle(): int
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

        $available = 0;
        $missing = [];

        foreach ($paths as $path) {
            $exists = collect(['evidence', 'local', 'public'])
                ->contains(fn (string $diskName) => Storage::disk($diskName)->exists($path));

            if ($exists) {
                $available++;
                continue;
            }

            $missing[] = $path;
        }

        $this->info("Evidence file check complete. Existing: {$available}. Missing: ".count($missing).'.');

        if ($this->option('show-missing') && $missing !== []) {
            foreach ($missing as $path) {
                $this->warn($path);
            }
        }

        return $missing === [] ? self::SUCCESS : self::FAILURE;
    }
}
