<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_subnets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('ip_pools')->onDelete('cascade');
            $table->string('network', 45);
            $table->integer('prefix_length');
            $table->string('gateway', 45)->nullable();
            $table->integer('vlan_id')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('pool_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_subnets');
    }
};
