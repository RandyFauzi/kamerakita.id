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
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_month'); // e.g., 'July 2026'
            $table->integer('total_minutes_billed');
            $table->decimal('total_amount_usd', 10, 2);
            $table->string('status')->default('unpaid_by_client'); // unpaid_by_client, paid_by_client
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_invoices');
    }
};
