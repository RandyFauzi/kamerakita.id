<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\ProcessCatchAllEmailService;

#[Signature('app:pull-mailbox-emails')]
#[Description('Dispatch job to fetch and process incoming emails from catch-all IMAP server')]
class PullMailboxEmailsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ProcessCatchAllEmailService $service)
    {
        $this->info('Starting email pull process synchronously...');
        
        $stats = $service->processEmails();
        
        $this->newLine();
        $this->info('====== IMAP SYNC STATS ======');
        $this->line('CONNECTED:       ' . ($stats['CONNECTED'] ? 'YES' : 'NO'));
        if ($stats['CONNECTED']) {
            $this->line('UIDVALIDITY:     ' . $stats['UIDVALIDITY']);
            $this->line('LAST_UID:        ' . $stats['LAST_UID']);
            $this->line('FETCHED_COUNT:   ' . $stats['FETCHED_COUNT']);
            $this->line('PROCESSED_COUNT: ' . $stats['PROCESSED_COUNT']);
            $this->line('SAVED_COUNT:     ' . $stats['SAVED_COUNT']);
            $this->line('DUPLICATE_COUNT: ' . $stats['DUPLICATE_COUNT']);
            $this->line('UNMATCHED_COUNT: ' . $stats['UNMATCHED_COUNT']);
            
            if ($stats['FAILED_COUNT'] > 0) {
                $this->error('FAILED_COUNT:    ' . $stats['FAILED_COUNT']);
            } else {
                $this->line('FAILED_COUNT:    0');
            }
            
            $this->line('NEW_LAST_UID:    ' . $stats['NEW_LAST_UID']);
        }
        $this->info('=============================');
        $this->newLine();
    }
}
