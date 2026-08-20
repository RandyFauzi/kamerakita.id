<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('partner_id')->constrained('partners')->onDelete('cascade');
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('video_url')->nullable();
            $table->string('imu_data_url')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('frequency_hz')->default(100);
            $table->enum('status', [
                'initiated', 
                'uploading', 
                'completed', 
                'failed', 
                'qc_pending', 
                'approved', 
                'rejected'
            ])->default('qc_pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};
