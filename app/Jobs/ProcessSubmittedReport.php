<?php

namespace App\Jobs;

use App\Models\VideoWorkReport;
use App\Services\EvidenceFileBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubmittedReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $report;

    /**
     * Create a new job instance.
     */
    public function __construct(VideoWorkReport $report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     */
    public function handle(EvidenceFileBackupService $backup): void
    {
        // 1. Backup Evidence
        try {
            if ($this->report->evidence_email_image_path) {
                $backup->backup($this->report->evidence_email_image_path);
            }
            if ($this->report->evidence_app_quality_image_path) {
                $backup->backup($this->report->evidence_app_quality_image_path);
            }
            if (!empty($this->report->evidence_submitted_image_paths)) {
                foreach ($this->report->evidence_submitted_image_paths as $path) {
                    $backup->backup($path);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to backup evidence in job', ['report_id' => $this->report->id, 'error' => $e->getMessage()]);
        }

        // 2. Generate Metadata
        // Implement metadata generation logic later
        Log::info('Metadata generation queued for report ' . $this->report->id);

        // 3. Notifications
        try {
            if (class_exists(\App\Jobs\SendWhatsAppMessageJob::class)) {
                $partner = $this->report->partner;
                // Assuming phone number is stored in partner or user model. We check just in case.
                $phone = $partner->whatsapp_number ?? $partner->phone ?? $partner->user->phone ?? null;
                
                if ($phone) {
                    $message = "Halo, laporan pekerjaan Anda untuk proyek {$this->report->project_name} pada tanggal {$this->report->submission_date->format('Y-m-d')} telah kami terima dan akan segera masuk antrean QC. Terima kasih!";
                    \App\Jobs\SendWhatsAppMessageJob::dispatch($phone, $message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch WA notification', ['error' => $e->getMessage()]);
        }

        // 4. Analytics
        // Implement analytics logic later
        Log::info('Analytics processing queued for report ' . $this->report->id);
    }
}
