<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected $apiUrl;
    protected $apiKey;
    protected $session;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->session = config('services.whatsapp.session');
    }

    /**
     * Dispatch WhatsApp message to the queue to be sent in the background.
     *
     * @param string $phone
     * @param string $message
     * @return void
     */
    public function queueMessage(string $phone, string $message): void
    {
        if (empty($phone)) {
            return;
        }

        \App\Jobs\SendWhatsAppMessageJob::dispatch($phone, $message);
    }

    /**
     * Send WhatsApp message to target phone number.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $phone, string $message): bool
    {
        if (empty($phone)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $phone,
                'message' => $message,
                'session' => $this->session,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message successfully sent to {$phone}.");
                return true;
            }

            Log::error("Failed to send WhatsApp message to {$phone}. Status: {$response->status()}, Response: {$response->body()}");
            return false;
        } catch (\Throwable $e) {
            Log::error("Exception occurred while sending WhatsApp message to {$phone}: {$e->getMessage()}");
            return false;
        }
    }
}
