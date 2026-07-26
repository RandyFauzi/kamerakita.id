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
        // Revert all unpaid approved daily reports to 'on_review' status
        // so that they can be re-evaluated and finalized using the new Period Approval system.
        DB::table('video_work_reports')
            ->where('qc_status', 'approved')
            ->where('payment_status', 'unpaid')
            ->update([
                'qc_status' => 'on_review',
                'approved_duration_minutes' => 0,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data transformation
    }
};
