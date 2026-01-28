<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Task 10.1: Add operator_id to device_monitors table
     */
    public function up(): void
    {
        Schema::table('device_monitors', function (Blueprint $table) {
            if (!Schema::hasColumn('device_monitors', 'operator_id')) {
                $table->foreignId('operator_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('users')
                    ->nullOnDelete();
                $table->index('operator_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_monitors', function (Blueprint $table) {
            if (Schema::hasColumn('device_monitors', 'operator_id')) {
                $table->dropForeign(['operator_id']);
                $table->dropIndex(['operator_id']);
                $table->dropColumn('operator_id');
            }
        });
    }
};
