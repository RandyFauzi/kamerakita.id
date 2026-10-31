<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;

class SyncMitraWorkersBankAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partner:sync-mitra-workers-bank {--force : Overwrite existing worker bank details with Mitra bank details}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync bank account details and mark onboarding complete for all workers under a Mitra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $workers = Partner::whereNotNull('mitra_parent_id')
            ->where('partner_role', 'worker')
            ->with(['mitraParent', 'user'])
            ->get();

        $this->info("Found {$workers->count()} worker(s) under a Mitra.");

        $updatedBankCount = 0;
        $updatedOnboardingCount = 0;

        foreach ($workers as $worker) {
            $mitra = $worker->mitraParent;
            if (!$mitra) {
                continue;
            }

            // Sync bank account from Mitra if empty or --force is passed
            $shouldUpdateBank = $force || empty($worker->bank_account_number);
            if ($shouldUpdateBank && !empty($mitra->bank_account_number)) {
                $worker->update([
                    'bank_name' => $mitra->bank_name,
                    'bank_account_number' => $mitra->bank_account_number,
                    'bank_account_owner' => $mitra->bank_account_owner,
                    'payment_method' => $mitra->payment_method ?: 'bank_transfer',
                    'airtm_username' => $mitra->airtm_username,
                ]);
                $updatedBankCount++;
            }

            // Complete onboarding for user
            if ($worker->user && !$worker->user->has_completed_onboarding) {
                $worker->user->update([
                    'has_completed_onboarding' => true,
                    'tos_accepted_at' => $worker->user->tos_accepted_at ?: now(),
                ]);
                $updatedOnboardingCount++;
            }
        }

        $this->info("Successfully synced bank details for {$updatedBankCount} worker(s).");
        $this->info("Successfully marked onboarding complete for {$updatedOnboardingCount} user(s).");

        return Command::SUCCESS;
    }
}
