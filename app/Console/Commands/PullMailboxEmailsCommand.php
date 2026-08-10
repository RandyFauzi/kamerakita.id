<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:pull-mailbox-emails')]
#[Description('Fetch and process incoming emails from catch-all IMAP server')]
class PullMailboxEmailsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ProcessCatchAllEmailService $service)
    {
        $this->info('Starting email pull process...');
        $service->processEmails();
        $this->info('Email pull process completed.');
    }
}
