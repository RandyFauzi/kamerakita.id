<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('referral_codes', 'activation_codes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('activation_codes', 'referral_codes');
    }
};
