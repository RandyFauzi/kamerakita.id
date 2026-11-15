<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_periods', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7)->unique();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('distribution')->nullable();
            $table->timestamps();
        });
        Schema::create('finance_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date')->index();
            $table->string('type', 20);
            $table->string('category', 80);
            $table->string('description');
            $table->unsignedBigInteger('amount');
            $table->foreignId('finance_period_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('finance_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('reserve_bps')->default(2000);
            $table->json('recipients');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_entries');
        Schema::dropIfExists('finance_settings');
        Schema::dropIfExists('finance_periods');
    }
};
