<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_label')->default('kamerakita-ai');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('frozen_on')->nullable();
            $table->decimal('client_billable_hours', 12, 2)->default(0);
            $table->decimal('client_rate_usd', 10, 2)->default(4.50);
            $table->decimal('client_total_usd', 14, 2)->default(0);
            $table->decimal('usd_to_idr_rate', 12, 2)->nullable();
            $table->bigInteger('revenue_idr')->default(0);
            $table->unsignedInteger('worker_rate_idr')->default(60000);
            $table->bigInteger('total_pay_idr')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payment_cycle_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_cycle_id')->constrained('payment_cycles')->cascadeOnDelete();
            $table->uuid('partner_id')->nullable()->index();
            $table->foreignId('atlas_worker_id')->nullable()->constrained('atlas_workers')->nullOnDelete();
            $table->string('worker_email')->index();
            $table->decimal('billable_hours', 10, 2)->default(0);
            $table->decimal('rejected_hours', 10, 2)->default(0);
            $table->decimal('not_checked_hours', 10, 2)->default(0);
            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('approved_share', 10, 6)->default(0);
            $table->unsignedInteger('recordings_count')->default(0);
            $table->string('main_rejection_reason')->nullable();
            $table->string('on_list_now')->default('Yes');
            $table->string('removed_from_program')->nullable();
            $table->bigInteger('amount_to_pay_idr')->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['payment_cycle_id', 'worker_email']);
            $table->foreign('partner_id')->references('id')->on('partners')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_cycle_entries');
        Schema::dropIfExists('payment_cycles');
    }
};
