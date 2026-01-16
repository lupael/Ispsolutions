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
        // Add tenant_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('tenant_id');
        });

        // Add tenant_id to network-related tables
        $tables = [
            'service_packages',
            'ip_pools',
            'ip_subnets',
            'ip_allocations',
            'network_users',
            'mikrotik_routers',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                    $table->index('tenant_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        $tables = [
            'service_packages',
            'ip_pools',
            'ip_subnets',
            'ip_allocations',
            'network_users',
            'mikrotik_routers',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
