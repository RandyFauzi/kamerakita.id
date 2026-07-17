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
        Schema::table('partners', function (Blueprint $table) {
            $table->string('nik')->unique()->nullable()->after('mitra_id');
            $table->string('email')->nullable()->after('whatsapp_number');
            $table->text('full_address')->nullable()->after('email');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_owner')->nullable()->after('bank_account_number');
            $table->string('smartphone_type')->nullable()->after('bank_account_owner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'email',
                'full_address',
                'bank_account_number',
                'bank_account_owner',
                'smartphone_type'
            ]);
        });
    }
};
