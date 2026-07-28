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
        Schema::table('partners', function (Blueprint $table) {
            // Personal referral code owned by Mitra/Rekruter — workers use this to link to them at registration
            $table->string('referral_code')->nullable()->unique()->after('group_name');

            // FK to the Mitra or Rekruter who recruited this worker
            $table->uuid('recruiter_partner_id')->nullable()->after('referral_code');
            $table->foreign('recruiter_partner_id')->references('id')->on('partners')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['recruiter_partner_id']);
            $table->dropColumn(['referral_code', 'recruiter_partner_id']);
        });
    }
};
