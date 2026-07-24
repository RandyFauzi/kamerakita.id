<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('group_name');
            $table->timestamps();
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('status');
        });

        // Insert initial referral codes for Group A & Group B
        $groupACodeId = (string) \Illuminate\Support\Str::uuid();
        $groupBCodeId = (string) \Illuminate\Support\Str::uuid();

        DB::table('referral_codes')->insert([
            [
                'id' => $groupACodeId,
                'code' => 'KMK-GROUP-A',
                'group_name' => 'Group A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $groupBCodeId,
                'code' => 'KMK-GROUP-B',
                'group_name' => 'Group B',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Default all existing partners to 'Group A'
        DB::table('partners')->update(['group_name' => 'Group A']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('group_name');
        });

        Schema::dropIfExists('referral_codes');
    }
};
