<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('referral_codes')->where('code', 'KMK-GROUP-A')->update(['code' => 'KMK-01ASQW']);
        DB::table('referral_codes')->where('code', 'KMK-GROUP-B')->update(['code' => 'KMK-02SADN']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('referral_codes')->where('code', 'KMK-01ASQW')->update(['code' => 'KMK-GROUP-A']);
        DB::table('referral_codes')->where('code', 'KMK-02SADN')->update(['code' => 'KMK-GROUP-B']);
    }
};
