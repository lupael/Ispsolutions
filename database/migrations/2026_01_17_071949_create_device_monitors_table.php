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
        Schema::create('device_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('monitorable_type'); // MikrotikRouter, Olt, Onu
            $table->unsignedBigInteger('monitorable_id');
            $table->string('status')->default('unknown'); // online, offline, degraded, unknown
            $table->decimal('cpu_usage', 5, 2)->nullable(); // 0.00 - 100.00
            $table->decimal('memory_usage', 5, 2)->nullable(); // 0.00 - 100.00
            $table->unsignedBigInteger('uptime')->nullable(); // in seconds
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['monitorable_type', 'monitorable_id']);
            $table->index(['tenant_id', 'status']);
            $table->index('last_check_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_monitors');
    }
};
