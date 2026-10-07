<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old atlas_tasks table because we are rebuilding it with a better schema
        Schema::dropIfExists('atlas_tasks');

        // Create atlas_workers table
        Schema::create('atlas_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('atlas_email')->unique();
            $table->string('group')->default('ASTRO');
            $table->boolean('active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // Recreate atlas_tasks table with new schema
        Schema::create('atlas_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlas_worker_id')->constrained('atlas_workers')->cascadeOnDelete();
            $table->string('atlas_task_id');
            $table->date('task_date');
            $table->string('task_name');
            $table->float('worked_minutes')->default(0);
            $table->float('approved_minutes')->default(0);
            $table->float('rejected_minutes')->default(0);
            $table->float('review_minutes')->default(0);
            $table->float('billable_minutes')->default(0);
            $table->string('status')->default('UNDER_REVIEW');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensures we don't have duplicate syncs for the same exact Atlas Task ID per worker
            $table->unique(['atlas_worker_id', 'atlas_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atlas_tasks');
        Schema::dropIfExists('atlas_workers');
    }
};
