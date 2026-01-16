<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subnet_id')->constrained('ip_subnets')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->string('mac_address', 17)->nullable();
            $table->string('username', 100)->nullable();
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->enum('status', ['allocated', 'released', 'reserved'])->default('allocated');
            $table->timestamps();

            $table->unique(['subnet_id', 'ip_address']);
            $table->index('status');
            $table->index('username');
            $table->index('mac_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_allocations');
    }
};
