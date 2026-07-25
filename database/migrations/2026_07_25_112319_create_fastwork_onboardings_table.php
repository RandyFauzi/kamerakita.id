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
        Schema::create('fastwork_onboardings', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('whatsapp_number');
            $table->string('device_type');
            $table->string('fastwork_username')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fastwork_onboardings');
    }
};
