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
        Schema::create('mcp_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('client_name')->index();
            $table->string('method');
            $table->string('tool_name')->nullable()->index();
            $table->json('payload')->nullable();
            $table->string('status')->index(); // success, error
            $table->text('error_message')->nullable();
            $table->decimal('execution_time_ms', 10, 2)->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_audit_logs');
    }
};
