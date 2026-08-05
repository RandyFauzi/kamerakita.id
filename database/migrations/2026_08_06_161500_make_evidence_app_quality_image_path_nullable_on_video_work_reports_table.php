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
        Schema::table('video_work_reports', function (Blueprint $table) {
            $table->string('evidence_app_quality_image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_work_reports', function (Blueprint $table) {
            $table->string('evidence_app_quality_image_path')->nullable(false)->change();
        });
    }
};
