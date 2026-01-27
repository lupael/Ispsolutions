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
        Schema::table('ip_allocations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('subnet_id')->constrained('users')->onDelete('cascade');
            $table->enum('allocation_type', ['static', 'dynamic'])->default('dynamic')->after('status');
            $table->index('user_id');
            $table->index('allocation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ip_allocations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['allocation_type']);
            $table->dropColumn(['user_id', 'allocation_type']);
        });
    }
};
