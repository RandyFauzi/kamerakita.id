<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::table('atlas_tasks')->selectRaw('LOWER(atlas_task_id) AS identity')->groupByRaw('LOWER(atlas_task_id)')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate recording IDs exist. Reconcile them before migrating; no rows were deleted.');
        }
        DB::table('atlas_tasks')->update(['atlas_task_id' => DB::raw('LOWER(atlas_task_id)')]);
        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->unique('atlas_task_id', 'atlas_tasks_recording_identity_unique');
            $table->string('ingest_source', 20)->default('atlas');
            $table->timestamp('source_observed_at')->nullable();
            $table->json('source_details')->nullable();
        });
        Schema::create('recording_import_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source', 20);
            $table->string('snapshot_hash', 64);
            $table->timestamp('observed_at');
            $table->unsignedInteger('expected_records');
            $table->unsignedInteger('expected_batches');
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('recording_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('import_id')->constrained('recording_import_runs')->cascadeOnDelete();
            $table->unsignedInteger('batch_index');
            $table->string('payload_hash', 64);
            $table->unsignedInteger('records');
            $table->unsignedInteger('written');
            $table->unsignedInteger('skipped');
            $table->json('record_ids');
            $table->unique(['import_id', 'batch_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recording_import_batches');
        Schema::dropIfExists('recording_import_runs');
        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->dropUnique('atlas_tasks_recording_identity_unique');
            $table->dropColumn(['ingest_source', 'source_observed_at', 'source_details']);
        });
    }
};
