<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence_file_backups', function (Blueprint $table) {
            $table->id();
            $table->string('path', 191)->unique();
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->mediumBlob('contents');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_file_backups');
    }
};
