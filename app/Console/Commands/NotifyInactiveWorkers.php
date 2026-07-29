<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Services\WhatsAppNotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class NotifyInactiveWorkers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partners:notify-inactive';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send engaging WhatsApp reminder to workers who have been inactive for exactly 2 days';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppNotificationService $waService)
    {
        $targetDate = Carbon::now()->subDays(2)->toDateString();
        
        $workers = Partner::where('partner_role', 'worker')
            ->where('status', 'active')
            ->withMax('videoWorkReports', 'submission_date')
            ->get();

        $notifiedCount = 0;

        foreach ($workers as $worker) {
            $latestDate = $worker->video_work_reports_max_submission_date;
            
            $isInactiveTwoDays = false;
            
            if ($latestDate && Carbon::parse($latestDate)->toDateString() === $targetDate) {
                $isInactiveTwoDays = true;
            } elseif (!$latestDate && $worker->created_at && $worker->created_at->toDateString() === $targetDate) {
                $isInactiveTwoDays = true;
            }

            if ($isInactiveTwoDays && $worker->whatsapp_number) {
                $message = "Haiii *{$worker->full_name}*,\n\nSayang banget lohh kamu sudah *2 hari* gak aktif bikin video di KameraKita. 😔\n\nPadahal potensi cuan melimpah sedang menunggumu! 💸 Kalau kamu ada masalah atau kendala apa pun, boleh banget sharing sama mimin ya. Semangattt terus pejuang cuan! 💪🔥\n\nYuk mulai lagi hari ini:\n" . route('dashboard');
                
                $waService->queueMessage($worker->whatsapp_number, $message);
                $this->info("Notified inactive worker: {$worker->full_name} ({$worker->whatsapp_number})");
                $notifiedCount++;
            }
        }

        $this->info("Completed! Total notified workers: {$notifiedCount}");
        return Command::SUCCESS;
    }
}
