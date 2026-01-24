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
        Schema::create('vpn_pool_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('vpn_pools')->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->boolean('is_allocated')->default(true);
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['pool_id', 'ip_address']);
            $table->index('is_allocated');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vpn_pool_allocations');
    }
};
