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
        Schema::create('recruiter_commissions', function (Blueprint $table) {
            $table->id();

            // The Rekruter who earns this commission
            $table->uuid('recruiter_partner_id');
            $table->foreign('recruiter_partner_id')->references('id')->on('partners')->cascadeOnDelete();

            // The Worker who triggered this commission milestone
            $table->uuid('worker_partner_id');
            $table->foreign('worker_partner_id')->references('id')->on('partners')->cascadeOnDelete();

            // Total approved hours the worker had when milestone was reached (e.g. 20)
            $table->decimal('approved_hours_at_milestone', 8, 2)->default(20.00);

            // Commission amount in IDR (default: Rp 100.000)
            $table->unsignedInteger('commission_amount')->default(100000);

            // Payment status
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Ensure only one commission record per recruiter-worker pair (one milestone per pair)
            $table->unique(['recruiter_partner_id', 'worker_partner_id'], 'rc_recruiter_worker_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_commissions');
    }
};
