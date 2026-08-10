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
            $table->index(['user_id', 'received_at'], 'idx_captured_emails_user_received');
            $table->index('received_at', 'idx_captured_emails_received_at');
            $table->index(['user_id', 'is_read'], 'idx_captured_emails_user_read');
            $table->index(['user_id', 'is_starred'], 'idx_captured_emails_user_starred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_emails', function (Blueprint $table) {
            $table->dropIndex('idx_captured_emails_user_received');
            $table->dropIndex('idx_captured_emails_received_at');
            $table->dropIndex('idx_captured_emails_user_read');
            $table->dropIndex('idx_captured_emails_user_starred');
        });
    }
};
