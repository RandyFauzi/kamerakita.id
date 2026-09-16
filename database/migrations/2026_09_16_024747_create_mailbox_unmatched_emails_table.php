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
        Schema::create('mailbox_unmatched_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email_account')->default('default')->index();
            $table->unsignedBigInteger('imap_uid');
            $table->unsignedBigInteger('imap_uidvalidity');
            $table->text('recipient')->nullable();
            $table->string('sender')->nullable();
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('reason');
            $table->timestamps();

            $table->unique(['email_account', 'imap_uidvalidity', 'imap_uid'], 'idx_unmatched_uid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_unmatched_emails');
    }
};
