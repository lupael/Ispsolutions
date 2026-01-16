<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_allocation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ip_allocation_id')->nullable()->constrained('ip_allocations')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->enum('action', ['allocate', 'release', 'reserve'])->comment('Action performed');
            $table->text('reason')->nullable();
            $table->timestamp('created_at');

            $table->index('ip_allocation_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_allocation_histories');
    }
};
