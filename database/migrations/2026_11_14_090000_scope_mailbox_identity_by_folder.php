<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('captured_emails', function (Blueprint $table) {
            $table->string('imap_account')->default('default');
            $table->string('imap_folder')->default('INBOX');
            $table->dropUnique('idx_captured_emails_uid_unique');
            $table->unique(['user_id', 'imap_account', 'imap_folder', 'imap_uidvalidity', 'imap_uid'], 'idx_captured_emails_folder_uid_unique');
        });
        Schema::table('mailbox_unmatched_emails', function (Blueprint $table) {
            $table->string('folder_name')->default('INBOX');
            $table->dropUnique('idx_unmatched_uid_unique');
            $table->unique(['email_account', 'folder_name', 'imap_uidvalidity', 'imap_uid'], 'idx_unmatched_folder_uid_unique');
        });
    }

    public function down(): void
    {
        // Folder-local UIDs may overlap after importing Junk. Never collapse
        // those identities or delete messages automatically on rollback.
        throw new RuntimeException('Folder-scoped mailbox identities require manual reconciliation before rollback.');
    }
};
