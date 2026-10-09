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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlas_worker_id')->constrained('atlas_workers')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->float('total_approved_minutes')->default(0);
            $table->decimal('amount_rupiah', 15, 2)->default(0);
            $table->string('status')->default('UNPAID'); // UNPAID, PAID
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->dropForeign(['payroll_id']);
            $table->dropColumn('payroll_id');
        });
        Schema::dropIfExists('payrolls');
    }
};
