<?php

namespace App\Jobs;

use App\Services\WhatsAppNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;

    public $tries = 5;
    public $backoff = [10, 60, 300, 900]; // 10s, 1m, 5m, 15m

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $message)
    {
        $this->phone = $phone;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppNotificationService $waService): void
    {
        $result = $waService->sendMessage($this->phone, $this->message);

        if ($result['status'] === 'temporary_error') {
            throw new \Exception("WhatsApp temporary failure: " . $result['message']);
        }

        if ($result['status'] === 'permanent_error') {
            $this->fail(new \Exception("WhatsApp permanent failure: " . $result['message']));
        }
    }
}
