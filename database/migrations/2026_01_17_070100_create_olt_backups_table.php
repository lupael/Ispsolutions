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
        Schema::create('olt_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('olts')->onDelete('cascade');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->enum('backup_type', ['auto', 'manual'])->default('manual');
            $table->timestamps();

            $table->index('olt_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt_backups');
    }
};
