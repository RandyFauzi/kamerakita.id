<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create `clients` table
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('tax_id')->nullable();
                $table->string('default_currency')->default('USD');
                $table->decimal('default_rate', 12, 2)->default(3.50);
                $table->timestamps();
            });
        }

        // 2. Add columns to `invoices` table
        if (!Schema::hasColumn('invoices', 'status')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
                $table->string('client_name')->nullable();
                $table->string('client_email')->nullable();
                $table->text('client_address')->nullable();
                $table->string('client_tax_id')->nullable();
                $table->decimal('unit_rate', 12, 2)->nullable();
                $table->string('currency')->default('USD');
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->decimal('source_approved_hours', 12, 2)->default(0);
                $table->decimal('billable_hours', 12, 2)->default(0);
                $table->decimal('adjustment_hours', 12, 2)->default(0);
                $table->text('adjustment_reason')->nullable();
                $table->string('status')->default('DRAFT'); // DRAFT, ISSUED, SENT, PAID, VOID
                $table->timestamp('voided_at')->nullable();
                $table->string('voided_by')->nullable();
                $table->text('void_reason')->nullable();
            });
        }
        
        // Handle change safely if possible
        try {
            Schema::table('invoices', function (Blueprint $table) {
                // Adjust existing columns to DECIMAL(12,2)
                $table->decimal('total_approved_hours', 12, 2)->change();
                $table->decimal('total_amount', 12, 2)->change();
            });
        } catch (\Exception $e) {}

        // 3. Create `invoice_items` table
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
                $table->string('description');
                $table->decimal('quantity', 12, 2);
                $table->string('unit');
                $table->decimal('unit_rate', 12, 2);
                $table->decimal('amount', 12, 2);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropUnique(['invoice_no']);
            $table->dropColumn([
                'client_id', 'client_name', 'client_email', 'client_address', 'client_tax_id',
                'unit_rate', 'currency', 'period_start', 'period_end',
                'source_approved_hours', 'billable_hours', 'adjustment_hours',
                'adjustment_reason', 'status', 'voided_at', 'voided_by', 'void_reason'
            ]);
            // Revert changes back to old data types if needed (typically handled carefully)
        });

        Schema::dropIfExists('clients');
    }
};
