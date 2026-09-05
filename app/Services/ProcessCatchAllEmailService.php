<?php

namespace App\Services;

use App\Models\User;
use App\Models\CapturedEmail;
use App\Models\MailboxSyncState;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessCatchAllEmailService
{
    public function processEmails(): void
    {
        Log::info("IMAP Catch-All: Menghubungkan ke server IMAP...");
        
        try {
            $client = Client::account('default');
            $client->connect();
            Log::info("IMAP Catch-All: Berhasil terhubung ke server!");

            $folder = $client->getFolder('INBOX');
            
            $status = $folder->getStatus();
            $currentUidvalidity = $status['uidvalidity'] ?? null;
            
            if (!$currentUidvalidity) {
                Log::error("IMAP Catch-All: Tidak bisa mendapatkan UIDVALIDITY. Menggunakan UID fallback (berisiko duplikat pada perubahan folder).");
                $currentUidvalidity = 1; // Fallback
            }

            // Dapatkan state sync terakhir (menganggap ini catch-all level sistem)
            $syncState = MailboxSyncState::firstOrCreate(
                ['folder_name' => 'INBOX'],
                ['uidvalidity' => $currentUidvalidity, 'last_uid' => 0]
            );

            // Cek jika UIDVALIDITY berubah (misalnya folder dihapus lalu dibuat ulang di server)
            if ($syncState->uidvalidity != $currentUidvalidity) {
                Log::warning("IMAP Catch-All: UIDVALIDITY berubah (Lama: {$syncState->uidvalidity}, Baru: {$currentUidvalidity}). Melakukan reset last_uid.");
                $syncState->update([
                    'uidvalidity' => $currentUidvalidity,
                    'last_uid' => 0
                ]);
            }

            $lastUid = $syncState->last_uid;
            
            // Tarik max 100 email dengan UID > last_uid untuk mencegah OOM
            Log::info("IMAP Catch-All: Menarik pesan dengan UID > {$lastUid}");
            $messages = $folder->query()->whereUid($lastUid . ':*')->limit(100)->get();

            $userMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => strtolower(trim($email)));
            $userPrefixMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => explode('@', strtolower(trim($email)))[0]);

            $highestUidProcessed = $lastUid;

            foreach ($messages as $message) {
                $uid = $message->getUid();
                
                // Skip if we accidentally got the last processed email again (IMAP inclusive range)
                if ($uid <= $lastUid) {
                    continue;
                }

                $subject = $message->getSubject() ?: '(Tanpa Subjek)';
                $messageId = $message->getMessageId() ?: null;
                
                $toAttribute = $message->getTo();
                $toAddresses = $toAttribute ? $toAttribute->all() : [];
                
                if (empty($toAddresses)) {
                    $altTo = $message->getAttributes()['delivered_to'] ?? $message->getAttributes()['envelope_to'] ?? null;
                    // Simplify: if no To address, we can't route it
                    if ($uid > $highestUidProcessed) {
                        $highestUidProcessed = $uid;
                    }
                    continue;
                }

                foreach ($toAddresses as $to) {
                    $emailAddress = strtolower(trim($to->mail));
                    $userId = $userMap->get($emailAddress) ?? $userPrefixMap->get(explode('@', $emailAddress)[0]);
                    
                    if ($userId) {
                        $senderAddress = strtolower(trim($message->getFrom()[0]->mail ?? 'unknown'));
                        $dateAttr = $message->getDate();
                        $receivedAt = ($dateAttr && $dateAttr->count() > 0) ? $dateAttr->first()->setTimezone(config('app.timezone'))->toDateTimeString() : now();
                        $content = $message->getHTMLBody() ?: $message->getTextBody();

                        try {
                            DB::beginTransaction();

                            // Use unique index for deduplication: user_id, imap_uidvalidity, imap_uid
                            // Fallback deduplication: user_id, message_id if UIDVALIDITY changed
                            $existing = CapturedEmail::where('user_id', $userId)
                                ->where('imap_uidvalidity', $currentUidvalidity)
                                ->where('imap_uid', $uid)
                                ->exists();

                            if (!$existing && $messageId) {
                                // Fallback check across all validities
                                $existing = CapturedEmail::where('user_id', $userId)
                                    ->where('message_id', $messageId)
                                    ->exists();
                            }

                            if (!$existing) {
                                CapturedEmail::create([
                                    'user_id' => $userId,
                                    'sender_address' => $senderAddress,
                                    'subject' => $subject,
                                    'message_content' => $content,
                                    'imap_uid' => $uid,
                                    'imap_uidvalidity' => $currentUidvalidity,
                                    'message_id' => $messageId,
                                    'received_at' => $receivedAt,
                                ]);
                                Log::info("IMAP Catch-All: Tersimpan untuk User ID $userId (UID: $uid)");
                            } else {
                                Log::info("IMAP Catch-All: Duplikat dilewati untuk User ID $userId (UID: $uid)");
                            }

                            DB::commit();
                        } catch (\Exception $dbErr) {
                            DB::rollBack();
                            Log::error("IMAP Catch-All: GAGAL MENYIMPAN KE DB: " . $dbErr->getMessage());
                        }
                    }
                }

                if ($uid > $highestUidProcessed) {
                    $highestUidProcessed = $uid;
                }
                
                unset($message, $content);
                if (gc_enabled()) gc_collect_cycles();
            }

            if ($highestUidProcessed > $syncState->last_uid) {
                $syncState->update(['last_uid' => $highestUidProcessed]);
                Log::info("IMAP Catch-All: State diperbarui. Last UID: {$highestUidProcessed}");
            }

        } catch (\Exception $e) {
            Log::error("IMAP Catch-All: TERJADI ERROR FATAL: " . $e->getMessage());
        }
    }
}
