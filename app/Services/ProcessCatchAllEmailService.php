<?php

namespace App\Services;

use App\Models\{User, CapturedEmail, MailboxSyncState};
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\{Log, DB, Cache, Schema};

class ProcessCatchAllEmailService
{
    private function emptyStats(): array
    {
        return ['CONNECTED' => false, 'UIDVALIDITY' => null, 'LAST_UID' => 0,
            'FETCHED_COUNT' => 0, 'PROCESSED_COUNT' => 0, 'SAVED_COUNT' => 0,
            'DUPLICATE_COUNT' => 0, 'UNMATCHED_COUNT' => 0, 'FAILED_COUNT' => 0, 'NEW_LAST_UID' => 0];
    }

    public function processEmails(): array
    {
        $stats = $this->emptyStats();
        $stats['FOLDERS'] = [];
        $lock = Cache::lock('mailbox:imap-sync', 600);
        if (!$lock->get()) {
            $stats['SKIPPED_LOCKED'] = true;
            return $stats;
        }
        $client = null;
        try {
            if (!Schema::hasColumn('captured_emails', 'imap_folder') || !Schema::hasColumn('mailbox_unmatched_emails', 'folder_name')) {
                throw new \RuntimeException('Run mailbox folder identity migration before syncing.');
            }
            $client = Client::account('default');
            $client->connect();
            $stats['CONNECTED'] = true;
            $folders = ['INBOX'];
            foreach ($client->getFolders(false) as $folder) {
                if (!$folder->no_select && (in_array(strtolower($folder->name), ['spam', 'junk', 'junk e-mail', 'junk email'], true)
                    || preg_match('~(?:^|[./])(?:spam|junk|junk e-mail|junk email)$~i', $folder->path))) {
                    $folders[] = $folder->path;
                }
            }
            foreach (array_unique($folders) as $path) {
                $result = $this->emptyStats();
                try {
                    $this->processFolder($client, $path, $result);
                } catch (\Throwable $error) {
                    $result['FAILED_COUNT']++;
                    Log::error('Mailbox folder sync failed', ['folder' => $path, 'error' => $error->getMessage()]);
                }
                $stats['FOLDERS'][$path] = $result;
                foreach (['FETCHED_COUNT', 'PROCESSED_COUNT', 'SAVED_COUNT', 'DUPLICATE_COUNT', 'UNMATCHED_COUNT', 'FAILED_COUNT'] as $key) {
                    $stats[$key] += $result[$key];
                }
                if ($path === 'INBOX') {
                    foreach (['UIDVALIDITY', 'LAST_UID', 'NEW_LAST_UID'] as $key) $stats[$key] = $result[$key];
                }
            }
        } catch (\Throwable $error) {
            $stats['FAILED_COUNT']++;
            Log::error('Mailbox sync failed', ['error' => $error->getMessage()]);
        } finally {
            try {
                if ($client) $client->disconnect();
            } catch (\Throwable $error) {
                $stats['FAILED_COUNT']++;
                Log::warning('Mailbox disconnect failed', ['error' => $error->getMessage()]);
            } finally {
                $lock->release();
            }
        }
        return $stats;
    }

    private function recipients($message): array
    {
        $attributes = $message->getAttributes();
        foreach (['x_original_to', 'original_to', 'delivered_to', 'envelope_to', 'to'] as $key) {
            $value = $attributes[$key] ?? null;
            if (is_object($value) && method_exists($value, 'all')) $value = $value->all();
            $addresses = [];
            foreach ((array) $value as $address) {
                $email = is_object($address) ? ($address->mail ?? '') : $address;
                if (is_string($email)) {
                    $email = strtolower(trim($email, " <>\t\r\n"));
                    // Hostinger may rewrite Delivered-To to the catch-all
                    // transport mailbox. That is not the worker recipient.
                    if ($key !== 'to' && $email === strtolower((string) config('imap.accounts.default.username'))) continue;
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) $addresses[] = $email;
                }
            }
            if ($addresses) return array_unique($addresses);
        }
        return [];
    }

    private function processFolder($client, string $folderPath, array &$stats): void
    {
            $folder = $client->getFolderByPath($folderPath);
            
            $status = $folder->getStatus();
            $currentUidvalidity = $status['uidvalidity'] ?? null;
            
            if (!$currentUidvalidity) {
                throw new \RuntimeException('Missing UIDVALIDITY for '.$folderPath);
            }
            $stats['UIDVALIDITY'] = $currentUidvalidity;

            $syncState = MailboxSyncState::firstOrCreate(
                ['email_account' => 'default', 'folder_name' => $folderPath],
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
            
            $searchUid = $lastUid + 1;
            Log::info("IMAP Catch-All: Menarik pesan dengan UID > {$lastUid} (Query UID: {$searchUid}:*)");
            $messages = $folder->query()->whereUid($searchUid . ':*')->limit(100)->get();
            
            $stats['FETCHED_COUNT'] = $messages->count();

            $userMap = User::pluck('id', 'email')->keyBy(fn ($id, $email) => strtolower(trim($email)));

            $highestUidProcessed = $lastUid;

            foreach ($messages as $message) {
                $uid = $message->getUid();
                
                if ($uid <= $lastUid) {
                    continue;
                }

                $subject = (string) $message->getSubject();
                $subject = $subject !== '' ? $subject : '(Tanpa Subjek)';
                $messageId = (string) $message->getMessageId();
                $messageId = $messageId !== '' ? $messageId : null;
                $timestamp = now()->toDateTimeString();
                $dateAttr = $message->getDate();
                if ($dateAttr && $dateAttr->count() > 0) {
                    $timestamp = $dateAttr->first()->setTimezone(config('app.timezone'))->toDateTimeString();
                }

                $resolvedRecipients = $this->recipients($message);

                if (empty($resolvedRecipients)) {
                    Log::warning("IMAP Catch-All: UNMATCHED_RECIPIENT (No Recipient Found)", [
                        'uid' => $uid,
                        'message_id' => $messageId,
                        'subject' => $subject,
                        'timestamp' => $timestamp
                    ]);
                    \App\Models\MailboxUnmatchedEmail::updateOrCreate(
                        ['email_account' => 'default', 'folder_name' => $folderPath, 'imap_uidvalidity' => $currentUidvalidity, 'imap_uid' => $uid],
                        [
                            'recipient' => null,
                            'sender' => strtolower(trim($message->getFrom()[0]->mail ?? 'unknown')),
                            'subject' => $subject,
                            'message_id' => $messageId,
                            'received_at' => $timestamp,
                            'reason' => 'No Recipient Found'
                        ]
                    );
                    $stats['UNMATCHED_COUNT']++;
                    $stats['PROCESSED_COUNT']++;
                    if ($uid > $highestUidProcessed) $highestUidProcessed = $uid;
                    $syncState->update(['last_uid' => $highestUidProcessed]);
                    continue;
                }

                $userId = null;
                $matchedRecipient = null;
                
                foreach ($resolvedRecipients as $recipientEmail) {
                    if ($userMap->has($recipientEmail)) {
                        $userId = $userMap->get($recipientEmail);
                        $matchedRecipient = $recipientEmail;
                        break;
                    }
                }
                
                if (!$userId) {
                    Log::warning("IMAP Catch-All: UNMATCHED_RECIPIENT", [
                        'recipient' => implode(', ', $resolvedRecipients),
                        'uid' => $uid,
                        'message_id' => $messageId,
                        'subject' => $subject,
                        'timestamp' => $timestamp
                    ]);
                    \App\Models\MailboxUnmatchedEmail::updateOrCreate(
                        ['email_account' => 'default', 'folder_name' => $folderPath, 'imap_uidvalidity' => $currentUidvalidity, 'imap_uid' => $uid],
                        [
                            'recipient' => implode(', ', $resolvedRecipients),
                            'sender' => strtolower(trim($message->getFrom()[0]->mail ?? 'unknown')),
                            'subject' => $subject,
                            'message_id' => $messageId,
                            'received_at' => $timestamp,
                            'reason' => 'Recipient not registered'
                        ]
                    );
                    $stats['UNMATCHED_COUNT']++;
                    $stats['PROCESSED_COUNT']++;
                    if ($uid > $highestUidProcessed) $highestUidProcessed = $uid;
                    $syncState->update(['last_uid' => $highestUidProcessed]);
                    continue;
                }

                // Email has a matched user. Now we process and save it.
                $senderAddress = strtolower(trim($message->getFrom()[0]->mail ?? 'unknown'));
                $content = $message->getHTMLBody() ?: $message->getTextBody();
                $processingSuccess = false;

                try {
                    DB::beginTransaction();

                    $existing = CapturedEmail::where('user_id', $userId)
                        ->where('imap_account', 'default')->where('imap_folder', $folderPath)
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
                            'imap_account' => 'default',
                            'imap_folder' => $folderPath,
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

                    \App\Models\MailboxUnmatchedEmail::where('email_account', 'default')
                        ->where('folder_name', $folderPath)->where('imap_uidvalidity', $currentUidvalidity)
                        ->where('imap_uid', $uid)->delete();
                    DB::commit();
                    $processingSuccess = true;
                    
                } catch (\Throwable $dbErr) {
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
                        $syncState->update(['last_uid' => $highestUidProcessed]);
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

    }
}
