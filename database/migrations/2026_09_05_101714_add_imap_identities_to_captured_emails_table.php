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
            $table->longText('message_content')->nullable()->change();
            $table->unsignedBigInteger('imap_uid')->nullable()->after('message_content');
            $table->unsignedBigInteger('imap_uidvalidity')->nullable()->after('imap_uid');
            $table->string('message_id')->nullable()->after('imap_uidvalidity');

            $table->unique(['user_id', 'imap_uidvalidity', 'imap_uid'], 'idx_captured_emails_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_emails', function (Blueprint $table) {
            $table->dropUnique('idx_captured_emails_uid_unique');
            $table->dropColumn(['imap_uid', 'imap_uidvalidity', 'message_id']);
            $table->text('message_content')->nullable()->change();
        });
    }
};
