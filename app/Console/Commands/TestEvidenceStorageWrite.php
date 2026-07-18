<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestEvidenceStorageWrite extends Command
{
    protected $signature = 'evidence:test-write';

    protected $description = 'Write, verify, and delete a temporary file on the evidence storage disk.';

    public function handle(): int
    {
        $disk = Storage::disk('evidence');
        $path = 'evidences/.health-check-'.now()->format('YmdHis').'.txt';
        $contents = 'kamerakita evidence storage health check';

        try {
            $disk->put($path, $contents);

            if (! $disk->exists($path) || $disk->get($path) !== $contents) {
                $this->error('Evidence storage test failed: file was not readable after write.');

                return self::FAILURE;
            }

            $disk->delete($path);
        } catch (\Throwable $exception) {
            $this->error('Evidence storage test failed: '.$exception->getMessage());
            $this->line('Pastikan folder storage/app/private bisa ditulis oleh user PHP/hosting.');

            return self::FAILURE;
        }

        $this->info('Evidence storage test passed. Upload folder is writable and readable.');

        return self::SUCCESS;
    }
}
