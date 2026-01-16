<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ip_subnet_id')->constrained('ip_subnets')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('allocation_type', ['static', 'dynamic'])->default('dynamic');
            $table->enum('status', ['active', 'reserved', 'released'])->default('active');
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['ip_subnet_id', 'ip_address']);
            $table->index('user_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_allocations');
    }
};
