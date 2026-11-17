<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend the partner_role ENUM to include 'main_account'.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `partners` MODIFY `partner_role` ENUM('worker', 'mitra', 'rekruter', 'main_account') NOT NULL");
        }
    }

    /**
     * Reverse the migration — remove 'main_account' from the ENUM.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `partners` MODIFY `partner_role` ENUM('worker', 'mitra', 'rekruter') NOT NULL");
        }
    }
};
