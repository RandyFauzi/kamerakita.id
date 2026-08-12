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
        Schema::table('fastwork_onboardings', function (Blueprint $table) {
            $table->boolean('has_headstrap')->default(false)->after('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fastwork_onboardings', function (Blueprint $table) {
            $table->dropColumn('has_headstrap');
        });
    }
};
