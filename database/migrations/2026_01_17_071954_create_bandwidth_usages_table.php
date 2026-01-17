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
        Schema::create('bandwidth_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('monitorable_type'); // MikrotikRouter, Olt, Onu
            $table->unsignedBigInteger('monitorable_id');
            $table->timestamp('timestamp')->useCurrent();
            $table->unsignedBigInteger('upload_bytes')->default(0);
            $table->unsignedBigInteger('download_bytes')->default(0);
            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->enum('period_type', ['raw', 'hourly', 'daily', 'weekly', 'monthly'])->default('raw');
            $table->timestamps();

            // Indexes for efficient time-series queries
            $table->index(['monitorable_type', 'monitorable_id', 'timestamp']);
            $table->index(['tenant_id', 'period_type', 'timestamp']);
            $table->index(['period_type', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bandwidth_usages');
    }
};
