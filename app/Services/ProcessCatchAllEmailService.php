<?php

namespace App\Services;

use App\Models\User;
use App\Models\CapturedEmail;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessCatchAllEmailService
{
    public function processEmails(): void
    {
        Log::info("IMAP Catch-All: Menghubungkan ke server IMAP Hostinger...");
        
        try {
            $client = Client::account('default');
            $client->connect();
            Log::info("IMAP Catch-All: Berhasil terhubung ke server!");

            $folder = $client->getFolder('INBOX');
            
            // Mengambil MAKSIMAL 50 pesan yang belum dibaca (Mencegah OOM)
            $messages = $folder->query()->unseen()->limit(50)->get();

            // Pre-load seluruh mapping email -> user_id ke memory (Menghindari N+1 Query)
            $userMap = \App\Models\User::pluck('id', 'email')->keyBy(fn ($id, $email) => strtolower(trim($email)));
            Log::info("IMAP Catch-All: Ditemukan " . $messages->count() . " pesan di INBOX.");

            $deletedCount = 0;

            foreach ($messages as $message) {
                $subject = $message->getSubject() ?: '(Tanpa Subjek)';
                
                // $message->getTo() returns an Attribute object.
                // We MUST call ->all() or ->toArray() to get the array of Address objects for iteration.
                $toAttribute = $message->getTo();
                $toAddresses = $toAttribute ? $toAttribute->all() : [];
                
                if (empty($toAddresses)) {
                    Log::info("IMAP Catch-All: Pesan '" . $subject . "' tidak memiliki header 'To' standar.");
                    
                    // Coba periksa header 'delivered-to' atau 'cc'
                    $altTo = $message->getAttributes()['delivered_to'] ?? $message->getAttributes()['envelope_to'] ?? null;
                    if ($altTo) {
                        Log::info("IMAP Catch-All: Ditemukan rute alternatif: " . json_encode($altTo));
                    }
                    
                    Log::info("IMAP Catch-All: Melewati pesan karena tidak ada alamat tujuan yang bisa diparsing.");
                    continue;
                }

                foreach ($toAddresses as $to) {
                    // Webklex otomatis mengambil email bersih di $to->mail
                    $emailAddress = strtolower(trim($to->mail));
                    
                    // Mencari user di database menggunakan $userMap
                    $userId = $userMap->get($emailAddress);
                    
                    if ($userId) {
                        Log::info("IMAP Catch-All: User DITEMUKAN! (ID: " . $userId . ")");
                        
                        $senderAddress = strtolower(trim($message->getFrom()[0]->mail ?? 'unknown'));
                        $dateAttr = $message->getDate();
                        if ($dateAttr && $dateAttr->count() > 0) {
                            $carbonDate = $dateAttr->first();
                            $carbonDate->setTimezone(config('app.timezone'));
                            $receivedAt = $carbonDate->toDateTimeString();
                        } else {
                            $receivedAt = now();
                        }
                        $content = $message->getHTMLBody() ?: $message->getTextBody();

                        try {
                            // Gunakan firstOrCreate agar TIDAK DUPLIKAT meskipun ditarik berkali-kali
                            $email = CapturedEmail::firstOrCreate(
                                [
                                    'user_id' => $userId,
                                    'subject' => $subject,
                                    'received_at' => $receivedAt,
                                ],
                                [
                                    'sender_address' => $senderAddress,
                                    'message_content' => $content,
                                ]
                            );

                            if ($email->wasRecentlyCreated) {
                                Log::info("IMAP Catch-All: BERHASIL: Pesan baru disimpan ke database!");
                            } else {
                                Log::info("IMAP Catch-All: DILEWATI: Pesan sudah ada di database (Anti-Duplikat).");
                            }

                        } catch (\Exception $dbErr) {
                            Log::error("IMAP Catch-All: GAGAL MENYIMPAN KE DB: " . $dbErr->getMessage());
                        }
                    } else {
                        Log::info("IMAP Catch-All: User tidak terdaftar di database (" . $emailAddress . "), pesan diabaikan.");
                    }
                }

                // Fitur Auto-Delete: Hapus pesan dari Hostinger jika umurnya lebih dari 14 hari
                $twoWeeksAgo = now()->subDays(14);
                $dateAttr = $message->getDate();
                $messageDate = ($dateAttr && $dateAttr->count() > 0) ? $dateAttr->first() : null;
                
                if ($messageDate && $messageDate->lessThan($twoWeeksAgo)) {
                    $message->delete();
                    $deletedCount++;
                    Log::info("IMAP Catch-All: Pesan ini berusia lebih dari 14 hari dan otomatis DIHAPUS dari server Hostinger.");
                }

                // Bebaskan memori per iterasi
                unset($message, $content);
                if (gc_enabled()) gc_collect_cycles();
            }

            if ($deletedCount > 0) {
                $client->expunge();
                Log::info("IMAP Catch-All: $deletedCount pesan kadaluarsa telah permanen dihapus dari Hostinger!");
            } else {
                Log::info("IMAP Catch-All: Selesai memproses semua email!");
            }

        } catch (\Exception $e) {
            Log::error("IMAP Catch-All: TERJADI ERROR FATAL: " . $e->getMessage());
        }
    }
}
