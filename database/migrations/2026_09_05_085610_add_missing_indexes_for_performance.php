<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('video_work_reports', function (Blueprint $table) {
                $table->index('partner_id');
                $table->index('qc_status');
                $table->index('payment_status');
                $table->index('submission_date');
                $table->index('created_at');
                $table->index(['partner_id', 'qc_status', 'payment_status'], 'vwr_partner_qc_payment_idx');
                $table->index(['partner_id', 'submission_date'], 'vwr_partner_submission_idx');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('partners', function (Blueprint $table) {
                $table->index('user_id');
                $table->index('mitra_parent_id');
                $table->index('partner_role');
                $table->index('status');
                $table->index('referral_code');
                $table->index(['mitra_parent_id', 'partner_role'], 'partners_mitra_role_idx');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('recordings', function (Blueprint $table) {
                $table->index('partner_id');
                $table->index('category_id');
                $table->index('status');
                $table->index('created_at');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_work_reports', function (Blueprint $table) {
            $table->dropIndex(['partner_id']);
            $table->dropIndex(['qc_status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['submission_date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex('vwr_partner_qc_payment_idx');
            $table->dropIndex('vwr_partner_submission_idx');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['mitra_parent_id']);
            $table->dropIndex(['partner_role']);
            $table->dropIndex(['status']);
            $table->dropIndex(['referral_code']);
            $table->dropIndex('partners_mitra_role_idx');
        });

        Schema::table('recordings', function (Blueprint $table) {
            $table->dropIndex(['partner_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
