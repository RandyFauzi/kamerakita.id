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
        Schema::table('captured_emails', function (Blueprint $table) {
            $table->index(['user_id', 'message_id'], 'idx_captured_emails_user_message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_emails', function (Blueprint $table) {
            $table->dropIndex('idx_captured_emails_user_message_id');
        });
    }
};
