<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\CapturedEmail;
use Carbon\Carbon;

class EmailWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Hostinger Agentic Mail
     */
    public function handle(Request $request)
    {
        // 0. Keamanan: Validasi Secret Token dari Hostinger
        $expectedToken = config('services.hostinger.webhook_secret');
        if ($expectedToken) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken !== $expectedToken) {
                Log::warning('Email Webhook: Invalid token received');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        // 1. Validasi Event
        $event = $request->input('event');
        if ($event !== 'message.received') {
            return response()->json(['message' => 'Event not handled'], 200);
        }

        $data = $request->input('data');
        if (!$data) {
            Log::warning('Email Webhook: Payload data kosong');
            return response()->json(['error' => 'No data payload'], 400);
        }

        try {
            $toAddresses = $data['to'] ?? [];
            if (!is_array($toAddresses)) {
                $toAddresses = [$toAddresses];
            }

            $subject = $data['subject'] ?? '(Tanpa Subjek)';
            
            // Ekstrak pengirim
            $fromRaw = $data['from'] ?? 'unknown';
            $senderAddress = $this->extractEmail($fromRaw) ?: $fromRaw;

            // Ekstrak waktu
            $dateRaw = $data['date'] ?? null;
            $receivedAt = $dateRaw ? Carbon::parse($dateRaw)->toDateTimeString() : now()->toDateTimeString();

            // Prioritaskan HTML, jika kosong gunakan Plain Text
            $content = !empty($data['plainHtml']) ? $data['plainHtml'] : (!empty($data['plainBody']) ? $data['plainBody'] : '(Isi pesan kosong)');

            $processedCount = 0;

            foreach ($toAddresses as $toRaw) {
                // Bersihkan alamat target
                $emailAddress = strtolower(trim($this->extractEmail($toRaw) ?: $toRaw));
                
                // Cari User berdasarkan email
                $user = User::where('email', $emailAddress)->first();
                
                if ($user) {
                    // Simpan atau abaikan jika duplikat
                    $email = CapturedEmail::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'subject' => $subject,
                            'received_at' => $receivedAt,
                        ],
                        [
                            'sender_address' => $senderAddress,
                            'message_content' => $content,
                        ]
                    );

                    if ($email->wasRecentlyCreated) {
                        Log::info("Email Webhook: Pesan baru disimpan untuk user ID {$user->id} ({$emailAddress})");
                    }
                    $processedCount++;
                }
            }

            return response()->json([
                'message' => 'Webhook processed successfully', 
                'processed_users' => $processedCount
            ], 200);

        } catch (\Exception $e) {
            Log::error('Email Webhook Error: ' . $e->getMessage());
            // Kembalikan 500 agar webhook mencatat error di log hPanel
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Helper: Mengekstrak "email@domain.com" dari string "Nama <email@domain.com>"
     */
    private function extractEmail(string $string): ?string
    {
        // Format: Nama <email@domain.com>
        if (preg_match('/<([^>]+)>/', $string, $matches)) {
            return trim($matches[1]);
        }
        
        // Format: email@domain.com
        if (filter_var(trim($string), FILTER_VALIDATE_EMAIL)) {
            return trim($string);
        }

        return null;
    }
}
