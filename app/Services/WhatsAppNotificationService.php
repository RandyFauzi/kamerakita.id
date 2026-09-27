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
    public function sendMessage(string $phone, string $message): array
    {
        $phone = \App\Helpers\PhoneHelper::formatForGateway($phone);

        if (empty($phone)) {
            return ['status' => 'permanent_error', 'message' => 'Invalid phone format'];
        }

        $maskedPhone = '***' . substr($phone, -4);

        try {
            $response = Http::timeout(20)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $phone,
                'message' => $message,
                'session' => $this->session,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message successfully sent to {$maskedPhone}.");
                return ['status' => 'success', 'message' => 'Delivered'];
            }

            $status = $response->status();
            Log::warning("Failed to send WhatsApp message to {$maskedPhone}. HTTP Status: {$status}");

            if (in_array($status, [400, 401, 403, 404, 422])) {
                return ['status' => 'permanent_error', 'message' => "HTTP {$status}"];
            }

            return ['status' => 'temporary_error', 'message' => "HTTP {$status}"];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("WhatsApp connection timeout/error to {$maskedPhone}");
            return ['status' => 'temporary_error', 'message' => 'Connection Exception'];
        } catch (\Throwable $e) {
            Log::error("WhatsApp unexpected error to {$maskedPhone}: " . substr($e->getMessage(), 0, 200));
            return ['status' => 'temporary_error', 'message' => 'Unexpected Exception'];
        }
    }
}
