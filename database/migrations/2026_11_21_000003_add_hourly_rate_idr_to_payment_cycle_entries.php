<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_cycle_entries', function (Blueprint $table) {
            $table->unsignedInteger('hourly_rate_idr')->default(60000)->after('billable_hours');
        });
    }

    public function down(): void
    {
        Schema::table('payment_cycle_entries', function (Blueprint $table) {
            $table->dropColumn('hourly_rate_idr');
        });
    }
};
