<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HandcapWaService
{
    /**
     * Send a text message via HANDCAP WA Gateway.
     *
     * @param string $phone The recipient phone number.
     * @param string $message The text message to send.
     * @param string $priority The priority ('normal' or 'high'). Default is 'normal'.
     * @param string $session The WA session name. Default is 'default'.
     * @return array|null Returns response array on success, or throws Exception on failure.
     */
    public function sendMessage(string $phone, string $message, string $priority = 'normal', string $session = 'default')
    {
        $apiKey = env('HANDCAP_WA_API_KEY');
        if (empty($apiKey)) {
            throw new \Exception("HANDCAP_WA_API_KEY is missing from .env");
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://handcap-by.autogrowthid.site/api/v1/send-message', [
            'phone' => $phone,
            'message' => $message,
            'session' => $session,
            'priority' => $priority
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("Failed to send WA Message: " . $response->body());
    }
}
