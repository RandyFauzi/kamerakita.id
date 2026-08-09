<?php

namespace App\Services;

use App\Models\User;
use App\Models\CapturedEmail;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Log;

class ProcessCatchAllEmailService
{
    /**
     * Connect to the IMAP server, pull unseen emails, and process them.
     */
    public function processEmails(): void
    {
        try {
            // Get the default client (defined in config/imap.php or via env)
            $client = Client::account('default');
            $client->connect();

            // Select the INBOX folder
            $folder = $client->getFolder('INBOX');

            // Fetch all UNSEEN emails
            $messages = $folder->query()->unseen()->get();

            foreach ($messages as $message) {
                // Extract recipient addresses from the 'To' header
                $toAddresses = $message->getTo();
                
                if (empty($toAddresses)) {
                    // Mark as read if it has no explicit recipient
                    $message->setFlag('Seen');
                    continue;
                }

                $processed = false;

                foreach ($toAddresses as $to) {
                    $emailAddress = strtolower($to->mail);
                    
                    // Look for a user with this email
                    $user = User::where('email', $emailAddress)->first();
                    
                    if ($user) {
                        // Save the email to the captured_emails table
                        CapturedEmail::create([
                            'user_id' => $user->id,
                            'sender_address' => $message->getFrom()[0]->mail ?? 'unknown',
                            'subject' => $message->getSubject() ?: '(No Subject)',
                            'message_content' => $message->getTextBody() ?: $message->getHTMLBody(),
                            'received_at' => $message->getDate() ? $message->getDate()->toDateTimeString() : now(),
                        ]);

                        $processed = true;
                    }
                }

                // If the email was processed (matched a user) or we want to clear the catch-all
                // Best practice for a catch-all is to delete them or mark them as read so they don't pile up.
                $message->delete(); 
            }

            // Expunge the deleted messages from the server
            $client->expunge();

        } catch (\Exception $e) {
            Log::error('Error processing IMAP catch-all emails: ' . $e->getMessage());
        }
    }
}
