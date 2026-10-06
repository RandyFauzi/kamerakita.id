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
        Schema::create('atlas_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index(); // Untuk pencocokan (user email)
            $table->date('task_date')->index();
            $table->string('task_name');
            $table->string('time_str')->nullable(); // e.g., "12:39 PM UTC"
            $table->decimal('approved_mins', 8, 2)->default(0);
            $table->decimal('rejected_mins', 8, 2)->default(0);
            $table->decimal('pending_mins', 8, 2)->default(0);
            $table->decimal('total_video_mins', 8, 2)->default(0);
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atlas_tasks');
    }
};
