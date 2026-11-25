<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('atlas_tasks')) {
            return;
        }

        Schema::table('atlas_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('atlas_tasks', 'task_date')) {
                $table->index('task_date', 'atlas_tasks_task_date_idx');
                $table->index(['atlas_worker_id', 'task_date'], 'atlas_tasks_worker_date_idx');
            }

            if (Schema::hasColumn('atlas_tasks', 'recorded_at')) {
                $table->index('recorded_at', 'atlas_tasks_recorded_at_idx');
            }

            if (Schema::hasColumn('atlas_tasks', 'payroll_id')) {
                $table->index('payroll_id', 'atlas_tasks_payroll_id_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('atlas_tasks')) {
            return;
        }

        Schema::table('atlas_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('atlas_tasks', 'task_date')) {
                $table->dropIndex('atlas_tasks_task_date_idx');
                $table->dropIndex('atlas_tasks_worker_date_idx');
            }

            if (Schema::hasColumn('atlas_tasks', 'recorded_at')) {
                $table->dropIndex('atlas_tasks_recorded_at_idx');
            }

            if (Schema::hasColumn('atlas_tasks', 'payroll_id')) {
                $table->dropIndex('atlas_tasks_payroll_id_idx');
            }
        });
    }
};
