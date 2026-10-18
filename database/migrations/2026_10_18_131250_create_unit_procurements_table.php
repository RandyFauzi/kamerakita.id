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
        Schema::create('unit_procurements', function (Blueprint $table) {
            $table->id();
            $table->string('procurement_code', 32)->unique();
            $table->string('item_name');
            $table->enum('category', [
                'camera',
                'lens',
                'smartphone',
                'audio_mic',
                'lighting_rig',
                'storage_pc',
                'accessory',
                'other'
            ])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->decimal('total_estimated_price', 15, 2)->default(0);
            $table->decimal('actual_total_price', 15, 2)->nullable();
            
            $table->string('vendor_name')->nullable();
            $table->text('purchase_url')->nullable();
            $table->text('purpose_notes')->nullable();
            
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'purchasing',
                'received',
                'rejected',
                'cancelled'
            ])->default('pending_approval');
            
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->string('receipt_path')->nullable();
            $table->string('serial_number')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
            $table->index('category');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_procurements');
    }
};
