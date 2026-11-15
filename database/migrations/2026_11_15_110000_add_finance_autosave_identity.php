<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_entries', function (Blueprint $table) {
            $table->uuid('client_key')->nullable()->unique();
            $table->unsignedInteger('revision')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('finance_entries', function (Blueprint $table) {
            $table->dropUnique(['client_key']);
            $table->dropColumn(['client_key', 'revision']);
        });
    }
};
