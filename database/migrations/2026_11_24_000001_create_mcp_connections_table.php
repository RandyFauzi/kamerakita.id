<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'account_version')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('account_version')->default(1)->after('role');
            });
        }

        Schema::create('mcp_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('oauth_client_id')->nullable()->index();
            $table->string('name');
            $table->string('client_type')->default('manual');
            $table->string('token_hash', 64)->nullable()->unique();
            $table->string('token_suffix', 16)->nullable();
            $table->text('token_encrypted')->nullable();
            $table->json('abilities')->nullable();
            $table->unsignedInteger('account_version')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'revoked_at']);
        });

        if (Schema::hasTable('mcp_audit_logs')) {
            Schema::table('mcp_audit_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('mcp_audit_logs', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('mcp_audit_logs', 'mcp_connection_id')) {
                    $table->uuid('mcp_connection_id')->nullable()->after('user_id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mcp_audit_logs')) {
            Schema::table('mcp_audit_logs', function (Blueprint $table) {
                if (Schema::hasColumn('mcp_audit_logs', 'mcp_connection_id')) {
                    $table->dropColumn('mcp_connection_id');
                }
                if (Schema::hasColumn('mcp_audit_logs', 'user_id')) {
                    $table->dropConstrainedForeignId('user_id');
                }
            });
        }

        Schema::dropIfExists('mcp_connections');

        if (Schema::hasColumn('users', 'account_version')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('account_version');
            });
        }
    }
};
