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
        // Migrate account_number to bank_account_number if empty
        DB::table('partners')
            ->where(function ($q) {
                $q->whereNull('bank_account_number')->orWhere('bank_account_number', '');
            })
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->update([
                'bank_account_number' => DB::raw('account_number')
            ]);

        // Migrate account_owner_name to bank_account_owner if empty
        DB::table('partners')
            ->where(function ($q) {
                $q->whereNull('bank_account_owner')->orWhere('bank_account_owner', '');
            })
            ->whereNotNull('account_owner_name')
            ->where('account_owner_name', '!=', '')
            ->update([
                'bank_account_owner' => DB::raw('account_owner_name')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One way migration, nothing to reverse for now
    }
};
