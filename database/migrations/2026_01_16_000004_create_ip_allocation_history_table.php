<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_allocation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_id')->nullable()->constrained('ip_allocations')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->string('mac_address', 17)->nullable();
            $table->string('username', 100)->nullable();
            $table->enum('action', ['allocated', 'released', 'updated'])->default('allocated');
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index('ip_address');
            $table->index('username');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_allocation_history');
    }
};
