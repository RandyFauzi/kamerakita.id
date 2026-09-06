<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('full_address');
            $table->string('payment_method', 50)->nullable()->after('bank_account_owner'); // bank_transfer, airtm
            $table->string('airtm_username')->nullable()->after('payment_method');
        });

        // Backfill existing data to Indonesian defaults
        DB::table('partners')->update([
            'country_code' => 'ID',
            'payment_method' => 'bank_transfer'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'payment_method', 'airtm_username']);
        });
    }
};
