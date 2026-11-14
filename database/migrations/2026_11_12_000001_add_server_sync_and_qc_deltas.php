<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->decimal('prev_approved_minutes', 16, 6)->nullable();
            $table->decimal('prev_rejected_minutes', 16, 6)->nullable();
            $table->timestamp('qc_changed_at')->nullable();
        });
        Schema::create('robotics_sync_runs', function (Blueprint $table) {
            $table->id();
            // One pending/running request across all web requests and schedulers.
            $table->string('active_slot')->nullable()->unique();
            $table->string('trigger');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status');
            $table->string('phase')->nullable();
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->json('result')->nullable();
            $table->string('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        Schema::table('recording_import_runs', fn (Blueprint $table) => $table->index(['source', 'snapshot_hash', 'observed_at'], 'recording_snapshot_lookup'));
    }

    public function down(): void
    {
        Schema::dropIfExists('robotics_sync_runs');
        Schema::table('recording_import_runs', fn (Blueprint $table) => $table->dropIndex('recording_snapshot_lookup'));
        Schema::table('atlas_tasks', fn (Blueprint $table) => $table->dropColumn(['prev_approved_minutes', 'prev_rejected_minutes', 'qc_changed_at']));
    }
};
