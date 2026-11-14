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
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("UPDATE atlas_tasks SET task_date = DATE(DATE_ADD(recorded_at, INTERVAL 8 HOUR)) WHERE recorded_at IS NOT NULL");
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE atlas_tasks SET task_date = DATE(datetime(recorded_at, '+8 hours')) WHERE recorded_at IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("UPDATE atlas_tasks SET task_date = DATE(recorded_at) WHERE recorded_at IS NOT NULL");
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE atlas_tasks SET task_date = DATE(recorded_at) WHERE recorded_at IS NOT NULL");
        }
    }
};
