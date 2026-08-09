<?php

namespace App\Services;

use App\Models\User;
use App\Models\CapturedEmail;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;

class ProcessCatchAllEmailService
{
    public function processEmails(): void
    {
        echo "🔍 Menghubungkan ke server IMAP Hostinger...\n";
        
        try {
            $client = Client::account('default');
            $client->connect();
            echo "✅ Berhasil terhubung ke server!\n";

            $folder = $client->getFolder('INBOX');
            
            // Mengambil SEMUA pesan (dibaca maupun belum)
            $messages = $folder->query()->all()->get();
            echo "📥 Ditemukan " . $messages->count() . " pesan di INBOX.\n";

            foreach ($messages as $message) {
                echo "\n---------------------------------\n";
                $subject = $message->getSubject() ?: '(Tanpa Subjek)';
                echo "📩 Memproses pesan: " . $subject . "\n";
                
                // $message->getTo() returns an Attribute object.
                // We MUST call ->all() or ->toArray() to get the array of Address objects for iteration.
                $toAttribute = $message->getTo();
                $toAddresses = $toAttribute ? $toAttribute->all() : [];
                
                if (empty($toAddresses)) {
                    echo "⚠️ Pesan tidak memiliki header 'To' standar.\n";
                    echo "🔍 Memeriksa header alternatif (Delivered-To, Cc, Bcc)...\n";
                    
                    // Coba periksa header 'delivered-to' atau 'cc'
                    $altTo = $message->getAttributes()['delivered_to'] ?? $message->getAttributes()['envelope_to'] ?? null;
                    if ($altTo) {
                        echo "✅ Ditemukan rute alternatif: " . json_encode($altTo) . "\n";
                    }
                    
                    // Mari kita dump seluruh header untuk melihat apa yang sebenarnya ada
                    $headers = $message->getAttributes();
                    $headerKeys = array_keys($headers);
                    echo "📋 Header yang tersedia: " . implode(', ', $headerKeys) . "\n";
                    
                    echo "⚠️ Melewati pesan karena tidak ada alamat tujuan yang bisa diparsing.\n";
                    continue;
                }

                foreach ($toAddresses as $to) {
                    // Webklex otomatis mengambil email bersih di $to->mail
                    $emailAddress = strtolower(trim($to->mail));
                    echo "🎯 Alamat tujuan terdeteksi: " . $emailAddress . "\n";
                    
                    // Mencari user di database KameraKita
                    $user = User::where('email', $emailAddress)->first();
                    
                    if ($user) {
                        echo "👤 User DITEMUKAN! (ID: " . $user->id . ")\n";
                        
                        $senderAddress = strtolower(trim($message->getFrom()[0]->mail ?? 'unknown'));
                        $receivedAt = $message->getDate() ? $message->getDate()->toDateTimeString() : now();
                        $content = $message->getTextBody() ?: $message->getHTMLBody();

                        try {
                            // Gunakan firstOrCreate agar TIDAK DUPLIKAT meskipun ditarik berkali-kali
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
                                echo "💾 BERHASIL: Pesan baru disimpan ke database!\n";
                            } else {
                                echo "⏭️ DILEWATI: Pesan sudah ada di database (Anti-Duplikat).\n";
                            }

                        } catch (\Exception $dbErr) {
                            echo "❌ GAGAL MENYIMPAN KE DB: " . $dbErr->getMessage() . "\n";
                        }
                    } else {
                        echo "❓ User tidak terdaftar di database, pesan diabaikan.\n";
                    }
                }
            }

            // Pesan TIDAK DIHAPUS dari Hostinger agar bisa kamu baca lagi jika perlu.
            echo "\n🎉 Selesai memproses semua email!\n";

        } catch (\Exception $e) {
            echo "\n💥 TERJADI ERROR FATAL: " . $e->getMessage() . "\n";
            Log::error('Error processing IMAP catch-all emails: ' . $e->getMessage());
        }
    }
}
