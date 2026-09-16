<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mailbox:prune-quarantine {--days=30 : The number of days to retain unmatched emails}')]
#[Description('Prune unmatched emails from the quarantine that are older than the specified days')]
class PruneMailboxQuarantine extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $date = now()->subDays($days);

        $count = \App\Models\MailboxUnmatchedEmail::where('created_at', '<', $date)->delete();

        $this->info("Pruned {$count} unmatched emails older than {$days} days.");
    }
}
