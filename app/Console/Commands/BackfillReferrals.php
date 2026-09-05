<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Partner;
use Illuminate\Support\Str;

class BackfillReferrals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kamerakita:backfill-referrals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing referral codes for existing Mitra and Rekruter';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $partners = Partner::whereIn('partner_role', ['mitra', 'rekruter'])
            ->whereNull('referral_code')
            ->get();

        $this->info("Found {$partners->count()} partner(s) without referral code.");

        foreach ($partners as $p) {
            do {
                $code = 'REF-' . strtoupper(Str::random(6));
            } while (Partner::where('referral_code', $code)->exists());

            $p->update(['referral_code' => $code]);
            $this->line("  {$p->full_name} ({$p->mitra_id}) -> {$code}");
        }

        $this->info("Done!");
    }
}
