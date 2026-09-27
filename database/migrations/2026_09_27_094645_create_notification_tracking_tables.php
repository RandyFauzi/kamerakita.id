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
        Schema::create('notification_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('target'); // all, workers, admins
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('total_targets')->default(0);
            $table->integer('delivered')->default(0);
            $table->integer('failed')->default(0);
            $table->string('status')->default('queued'); // queued, processing, completed
            $table->timestamps();
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('notification_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel'); // webpush, whatsapp
            $table->string('status')->default('queued'); // queued, sent, failed, expired
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // Migrate endpoint in push_subscriptions to support longer URLs (D)
        // Since we can't easily alter a unique index length on MySQL without dropping it,
        // and we don't strictly need DB-level uniqueness (we can do it on application level or it's fine).
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropUnique(['endpoint']);
        });

        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->text('endpoint')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('endpoint', 500)->change();
            $table->unique('endpoint');
        });
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_campaigns');
    }
};
