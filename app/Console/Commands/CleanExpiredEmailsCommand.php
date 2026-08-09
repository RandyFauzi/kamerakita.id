<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CapturedEmail;

class CleanExpiredEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-expired-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean captured emails older than 14 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning expired emails...');
        $deleted = CapturedEmail::where('received_at', '<', now()->subDays(14))->delete();
        $this->info("Deleted {$deleted} expired emails.");
    }
}
