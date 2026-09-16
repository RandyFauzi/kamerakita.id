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
        Schema::table('mailbox_sync_states', function (Blueprint $table) {
            $table->unique(['email_account', 'folder_name'], 'idx_sync_states_email_folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mailbox_sync_states', function (Blueprint $table) {
            $table->dropUnique('idx_sync_states_email_folder');
        });
    }
};
