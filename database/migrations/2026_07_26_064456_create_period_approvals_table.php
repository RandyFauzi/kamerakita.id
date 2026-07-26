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
        Schema::create('period_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('partner_id');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->integer('approved_minutes')->default(0);
            $table->string('status')->default('draft'); // 'draft', 'approved', 'paid'
            $table->text('verifier_notes')->nullable();
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->unique(['partner_id', 'period_start_date', 'period_end_date'], 'partner_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_approvals');
    }
};
