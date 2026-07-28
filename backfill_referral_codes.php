<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Partner;
use Illuminate\Support\Str;

$partners = Partner::whereIn('partner_role', ['mitra', 'rekruter'])
    ->whereNull('referral_code')
    ->get();

echo "Found {$partners->count()} partner(s) without referral code.\n";

foreach ($partners as $p) {
    do {
        $code = 'REF-' . strtoupper(Str::random(6));
    } while (Partner::where('referral_code', $code)->exists());

    $p->update(['referral_code' => $code]);
    echo "  {$p->full_name} ({$p->mitra_id}) -> {$code}\n";
}

echo "Done!\n";
