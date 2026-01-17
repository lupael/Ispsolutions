<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('device_type'); // router, switch, olt, onu, ap
            $table->string('ip_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'device_type']);
        });

        Schema::create('network_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('source_device_id')->constrained('network_devices')->cascadeOnDelete();
            $table->foreignId('target_device_id')->constrained('network_devices')->cascadeOnDelete();
            $table->string('link_type')->default('ethernet'); // ethernet, fiber, wireless
            $table->integer('bandwidth')->nullable(); // Mbps
            $table->string('status')->default('active');
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_links');
        Schema::dropIfExists('network_devices');
    }
};
