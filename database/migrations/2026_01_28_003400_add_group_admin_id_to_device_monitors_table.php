<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Task 10.1: Add group_admin_id to device_monitors table
     */
    public function up(): void
    {
        Schema::table('device_monitors', function (Blueprint $table) {
            if (!Schema::hasColumn('device_monitors', 'group_admin_id')) {
                $table->foreignId('group_admin_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('users')
                    ->nullOnDelete();
                $table->index('group_admin_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_monitors', function (Blueprint $table) {
            if (Schema::hasColumn('device_monitors', 'group_admin_id')) {
                $table->dropForeign(['group_admin_id']);
                $table->dropIndex(['group_admin_id']);
                $table->dropColumn('group_admin_id');
            }
        });
    }
};
