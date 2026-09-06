<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Jobs\SyncMailboxJob;

#[Signature('app:pull-mailbox-emails')]
#[Description('Dispatch job to fetch and process incoming emails from catch-all IMAP server')]
class PullMailboxEmailsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ProcessCatchAllEmailService $service)
    {
        $this->info('Starting email pull process synchronously...');
        $service->processEmails();
        $this->info('Finished.');
    }
}
