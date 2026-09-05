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
        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->string('project')->default('atlas'); // e.g. atlas, minutes_data, dll.
            $table->string('partner_role'); // e.g. worker, mitra, vendor, commission
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->decimal('rate_per_hour', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_cards');
    }
};
