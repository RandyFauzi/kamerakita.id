<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_cycle_entries', function (Blueprint $table) {
            $table->boolean('was_auto_created')->default(false)->after('amount_to_pay_idr');
        });
    }

    public function down(): void
    {
        Schema::table('payment_cycle_entries', function (Blueprint $table) {
            $table->dropColumn('was_auto_created');
        });
    }
};
