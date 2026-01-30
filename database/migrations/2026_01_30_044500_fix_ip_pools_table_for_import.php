<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: This migration uses MySQL-specific ALTER TABLE syntax for ENUM modification.
     * For non-MySQL databases, the migration will need to be adapted.
     * 
     * tenant_id and nas_id are intentionally added without foreign key constraints
     * to allow for flexible installation scenarios where referenced tables may not exist.
     */
    public function up(): void
    {
        // Add missing columns and fix pool_type enum
        Schema::table('ip_pools', function (Blueprint $table) {
            // Check if these columns don't exist
            if (!Schema::hasColumn('ip_pools', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('description');
            }
            if (!Schema::hasColumn('ip_pools', 'subnet_mask')) {
                $table->string('subnet_mask', 45)->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('ip_pools', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('status');
                $table->index('tenant_id');
            }
            if (!Schema::hasColumn('ip_pools', 'nas_id')) {
                $table->unsignedBigInteger('nas_id')->nullable()->after('tenant_id');
                $table->index('nas_id');
            }
        });

        // Modify the pool_type enum to add 'pppoe' and other types (MySQL-specific)
        DB::statement("ALTER TABLE `ip_pools` MODIFY COLUMN `pool_type` ENUM('public', 'private', 'pppoe', 'dhcp', 'static') DEFAULT 'public'");
        
        // Modify status enum to include 'available'
        DB::statement("ALTER TABLE `ip_pools` MODIFY COLUMN `status` ENUM('active', 'inactive', 'available', 'allocated') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum changes
        DB::statement("ALTER TABLE `ip_pools` MODIFY COLUMN `pool_type` ENUM('public', 'private') DEFAULT 'public'");
        DB::statement("ALTER TABLE `ip_pools` MODIFY COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active'");

        Schema::table('ip_pools', function (Blueprint $table) {
            if (Schema::hasColumn('ip_pools', 'nas_id')) {
                $table->dropIndex(['nas_id']);
                $table->dropColumn('nas_id');
            }
            if (Schema::hasColumn('ip_pools', 'tenant_id')) {
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            if (Schema::hasColumn('ip_pools', 'subnet_mask')) {
                $table->dropColumn('subnet_mask');
            }
            if (Schema::hasColumn('ip_pools', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
};
