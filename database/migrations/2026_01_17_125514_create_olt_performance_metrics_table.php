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
        Schema::create('olt_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('olt_id')->constrained()->onDelete('cascade');
            $table->decimal('cpu_usage', 5, 2)->nullable();
            $table->decimal('memory_usage', 5, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->unsignedBigInteger('bandwidth_rx')->nullable()->comment('Bytes per second'); // bytes
            $table->unsignedBigInteger('bandwidth_tx')->nullable()->comment('Bytes per second'); // bytes
            $table->integer('total_onus')->default(0);
            $table->integer('online_onus')->default(0);
            $table->integer('offline_onus')->default(0);
            $table->json('port_utilization')->nullable();
            $table->timestamps();
            
            $table->index(['olt_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt_performance_metrics');
    }
};
