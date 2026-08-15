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
        Schema::table('video_work_reports', function (Blueprint $table) {
            $table->integer('rate_applied')->nullable()->after('approved_duration_minutes');
        });

        // Backfill data: set rate_applied to the partner's base_hourly_rate for existing records
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                UPDATE video_work_reports vwr
                JOIN partners p ON vwr.partner_id = p.id
                SET vwr.rate_applied = COALESCE(p.base_hourly_rate, 50000)
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_work_reports', function (Blueprint $table) {
            $table->dropColumn('rate_applied');
        });
    }
};
