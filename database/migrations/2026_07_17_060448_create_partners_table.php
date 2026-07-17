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
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('partner_role', ['worker', 'mitra']);
            $table->uuid('contributor_id')->nullable(); // will be renamed to mitra_parent_id in later migration
            $table->string('mitra_id')->unique();
            $table->string('full_name');
            $table->string('whatsapp_number');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_owner_name')->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->integer('base_hourly_rate')->default(54000);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Self-referencing foreign key constraint
            $table->foreign('contributor_id')->references('id')->on('partners')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
