<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->double('deferred_minutes')->default(0);
            $table->string('source_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('atlas_tasks', function (Blueprint $table) {
            $table->dropColumn(['deferred_minutes', 'source_status']);
        });
    }
};
