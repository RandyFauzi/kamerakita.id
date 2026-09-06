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
    public function processEmails(): array
    {
        $stats = [
            'CONNECTED' => false,
            'UIDVALIDITY' => null,
            'LAST_UID' => 0,
            'FETCHED_COUNT' => 0,
            'PROCESSED_COUNT' => 0,
            'SAVED_COUNT' => 0,
            'DUPLICATE_COUNT' => 0,
            'UNMATCHED_COUNT' => 0,
            'FAILED_COUNT' => 0,
            'NEW_LAST_UID' => 0,
        ];

        Log::info("IMAP Catch-All: Menghubungkan ke server IMAP...");
        
        try {
            $client = Client::account('default');
            $client->connect();
            Log::info("IMAP Catch-All: Berhasil terhubung ke server!");
            $stats['CONNECTED'] = true;

            $folder = $client->getFolder('INBOX');
            
            $status = $folder->getStatus();
            $currentUidvalidity = $status['uidvalidity'] ?? null;
            
            if (!$currentUidvalidity) {
                Log::error("IMAP Catch-All: Tidak bisa mendapatkan UIDVALIDITY. Menggunakan UID fallback (berisiko duplikat pada perubahan folder).");
                $currentUidvalidity = 1; // Fallback
            }
            $stats['UIDVALIDITY'] = $currentUidvalidity;

            $syncState = MailboxSyncState::firstOrCreate(
                ['folder_name' => 'INBOX'],
                ['uidvalidity' => $currentUidvalidity, 'last_uid' => 0]
            );

            if ($syncState->uidvalidity != $currentUidvalidity) {
                Log::warning("IMAP Catch-All: UIDVALIDITY berubah (Lama: {$syncState->uidvalidity}, Baru: {$currentUidvalidity}). Melakukan reset last_uid.");
                $syncState->update([
                    'uidvalidity' => $currentUidvalidity,
                    'last_uid' => 0
                ]);
            }

            $lastUid = $syncState->last_uid;
            $stats['LAST_UID'] = $lastUid;
            
            $searchUid = $lastUid > 0 ? $lastUid : 1;
            Log::info("IMAP Catch-All: Menarik pesan dengan UID > {$lastUid} (Query UID: {$searchUid}:*)");
            $messages = $folder->query()->whereUid($searchUid . ':*')->limit(100)->get();
            
            $stats['FETCHED_COUNT'] = $messages->count();

            $userMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => strtolower(trim($email)));
            $userPrefixMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => explode('@', strtolower(trim($email)))[0]);

            $highestUidProcessed = $lastUid;

            foreach ($messages as $message) {
                $uid = $message->getUid();
                
                if ($uid <= $lastUid) {
                    continue;
                }

                $subject = $message->getSubject() ?: '(Tanpa Subjek)';
                $messageId = $message->getMessageId() ?: null;
                $timestamp = now()->toDateTimeString();
                $dateAttr = $message->getDate();
                if ($dateAttr && $dateAttr->count() > 0) {
                    $timestamp = $dateAttr->first()->setTimezone(config('app.timezone'))->toDateTimeString();
                }

                // 2. CRITICAL - recipient extraction (Prioritize Envelope/Delivered-To over Header To)
                $resolvedRecipients = [];
                
                $attributes = $message->getAttributes();
                $deliveredTo = $attributes['delivered_to'] ?? $attributes['envelope_to'] ?? null;
                
                // If delivered_to exists and is string or array, parse it
                if ($deliveredTo) {
                    if (is_array($deliveredTo)) {
                        foreach ($deliveredTo as $dt) {
                            $resolvedRecipients[] = strtolower(trim($dt));
                        }
                    } elseif (is_string($deliveredTo)) {
                        $resolvedRecipients[] = strtolower(trim($deliveredTo));
                    }
                }

                // Fallback to Header To if Envelope is empty
                if (empty($resolvedRecipients)) {
                    $toAttribute = $message->getTo();
                    $toAddresses = $toAttribute ? $toAttribute->all() : [];
                    foreach ($toAddresses as $to) {
                        if (isset($to->mail)) {
                            $resolvedRecipients[] = strtolower(trim($to->mail));
                        }
                    }
                }
                
                // Clean up any empty strings
                $resolvedRecipients = array_filter($resolvedRecipients);

                if (empty($resolvedRecipients)) {
                    // Permanently unroutable (no recipient whatsoever). We must advance cursor to avoid infinite loop.
                    Log::warning("IMAP Catch-All: UNMATCHED_RECIPIENT (No Recipient Found)", [
                        'uid' => $uid,
                        'message_id' => $messageId,
                        'subject' => $subject,
                        'timestamp' => $timestamp
                    ]);
                    $stats['UNMATCHED_COUNT']++;
                    $stats['PROCESSED_COUNT']++;
                    if ($uid > $highestUidProcessed) $highestUidProcessed = $uid;
                    continue;
                }

                // Try to find a matching user
                $userId = null;
                $matchedRecipient = null;
                
                foreach ($resolvedRecipients as $recipientEmail) {
                    // Exact Match (Prioritized)
                    if ($userMap->has($recipientEmail)) {
                        $userId = $userMap->get($recipientEmail);
                        $matchedRecipient = $recipientEmail;
                        break;
                    }
                }
                
                // Prefix Match Fallback
                if (!$userId) {
                    foreach ($resolvedRecipients as $recipientEmail) {
                        $prefix = explode('@', $recipientEmail)[0];
                        if ($userPrefixMap->has($prefix)) {
                            $userId = $userPrefixMap->get($prefix);
                            $matchedRecipient = $recipientEmail;
                            break;
                        }
                    }
                }

                if (!$userId) {
                    // Permanently unroutable (no matching user in DB). We must advance cursor to avoid infinite loop.
                    Log::warning("IMAP Catch-All: UNMATCHED_RECIPIENT", [
                        'recipient' => implode(', ', $resolvedRecipients),
                        'uid' => $uid,
                        'message_id' => $messageId,
                        'subject' => $subject,
                        'timestamp' => $timestamp
                    ]);
                    $stats['UNMATCHED_COUNT']++;
                    $stats['PROCESSED_COUNT']++;
                    if ($uid > $highestUidProcessed) $highestUidProcessed = $uid;
                    continue;
                }

                // Email has a matched user. Now we process and save it.
                $senderAddress = strtolower(trim($message->getFrom()[0]->mail ?? 'unknown'));
                $content = $message->getHTMLBody() ?: $message->getTextBody();
                $processingSuccess = false;

                try {
                    DB::beginTransaction();

                    $existing = CapturedEmail::where('user_id', $userId)
                        ->where('imap_uidvalidity', $currentUidvalidity)
                        ->where('imap_uid', $uid)
                        ->exists();

                    if (!$existing && $messageId) {
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
                            'received_at' => $timestamp,
                        ]);
                        Log::info("IMAP Catch-All: Tersimpan untuk User ID $userId (UID: $uid, Recipient: $matchedRecipient)");
                        $stats['SAVED_COUNT']++;
                    } else {
                        Log::info("IMAP Catch-All: Duplikat dilewati untuk User ID $userId (UID: $uid)");
                        $stats['DUPLICATE_COUNT']++;
                    }

                    DB::commit();
                    $processingSuccess = true;
                    
                } catch (\Exception $dbErr) {
                    DB::rollBack();
                    Log::error("IMAP Catch-All: GAGAL MENYIMPAN KE DB", [
                        'uid' => $uid,
                        'recipient' => $matchedRecipient,
                        'message_id' => $messageId,
                        'error' => $dbErr->getMessage()
                    ]);
                    $stats['FAILED_COUNT']++;
                    // CRITICAL: Stop cursor advancement and break loop immediately.
                    // This email will be retried on next sync.
                    break;
                }

                if ($processingSuccess) {
                    $stats['PROCESSED_COUNT']++;
                    if ($uid > $highestUidProcessed) {
                        $highestUidProcessed = $uid;
                    }
                }
                
                unset($message, $content);
                if (gc_enabled()) gc_collect_cycles();
            }

            if ($highestUidProcessed > $syncState->last_uid) {
                $syncState->update(['last_uid' => $highestUidProcessed]);
                Log::info("IMAP Catch-All: State diperbarui. Last UID: {$highestUidProcessed}");
            }
            $stats['NEW_LAST_UID'] = $highestUidProcessed;

        } catch (\Exception $e) {
            Log::error("IMAP Catch-All: TERJADI ERROR FATAL: " . $e->getMessage());
        }

        return $stats;
    }
}
