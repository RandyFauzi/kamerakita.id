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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE partners MODIFY status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('partners')
            ->where('status', 'inactive')
            ->update(['status' => 'active']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE partners MODIFY status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
        }
    }
};
