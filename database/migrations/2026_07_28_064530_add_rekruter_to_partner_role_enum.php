<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extend the partner_role ENUM to include 'rekruter'.
     * Uses raw ALTER TABLE because Laravel's Blueprint doesn't support
     * modifying ENUM values directly without dropping and recreating.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `partners` MODIFY `partner_role` ENUM('worker', 'mitra', 'rekruter') NOT NULL");
        }
    }

    /**
     * Reverse the migration — remove 'rekruter' from the ENUM.
     * This will only work safely if no rows have partner_role = 'rekruter'.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `partners` MODIFY `partner_role` ENUM('worker', 'mitra') NOT NULL");
        }
    }
};
