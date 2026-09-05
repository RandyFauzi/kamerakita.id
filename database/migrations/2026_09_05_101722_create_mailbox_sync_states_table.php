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
        Schema::create('mailbox_sync_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email_account')->nullable(); // Just in case we sync by email instead of user_id
            $table->string('folder_name')->default('INBOX');
            $table->unsignedBigInteger('uidvalidity');
            $table->unsignedBigInteger('last_uid')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'folder_name'], 'idx_mailbox_sync_states_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_sync_states');
    }
};
