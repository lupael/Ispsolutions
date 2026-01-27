<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds network-related fields from NetworkUser model to users table.
     * This enables the Customer (User) to be the single source of truth
     * for both CRM and network provisioning (RADIUS/Router).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Network service fields
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('email');
                $table->index('username');
            }
            
            // Store plain text password for RADIUS (separate from hashed login password)
            if (!Schema::hasColumn('users', 'radius_password')) {
                $table->string('radius_password')->nullable()->after('password');
            }
            
            if (!Schema::hasColumn('users', 'service_type')) {
                $table->enum('service_type', ['pppoe', 'hotspot', 'static', 'dhcp', 'vpn', 'cable_tv'])->nullable()->after('radius_password');
                $table->index('service_type');
            }
            
            if (!Schema::hasColumn('users', 'connection_type')) {
                $table->enum('connection_type', ['pppoe', 'hotspot', 'static', 'dhcp', 'vpn'])->nullable()->after('service_type');
            }
            
            if (!Schema::hasColumn('users', 'billing_type')) {
                $table->enum('billing_type', ['prepaid', 'postpaid', 'unlimited'])->nullable()->after('connection_type');
            }
            
            if (!Schema::hasColumn('users', 'device_type')) {
                $table->string('device_type', 100)->nullable()->after('billing_type');
            }
            
            // Network identifiers
            if (!Schema::hasColumn('users', 'mac_address')) {
                $table->string('mac_address', 17)->nullable()->after('device_type');
                $table->index('mac_address');
            }
            
            if (!Schema::hasColumn('users', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('mac_address');
                $table->index('ip_address');
            }
            
            // Service status and expiry
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('is_active');
                $table->index('status');
            }
            
            if (!Schema::hasColumn('users', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('status');
                $table->index('expiry_date');
            }
            
            // Zone assignment for network management
            if (!Schema::hasColumn('users', 'zone_id') && Schema::hasTable('zones')) {
                $table->foreignId('zone_id')->nullable()->after('tenant_id')->constrained('zones')->nullOnDelete();
                $table->index('zone_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $columns = ['username', 'service_type', 'mac_address', 'ip_address', 'status', 'expiry_date', 'zone_id'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    // Drop foreign key for zone_id
                    if ($column === 'zone_id') {
                        $table->dropForeign(['zone_id']);
                    }
                    // Drop indexes
                    try {
                        $table->dropIndex(['users_' . $column . '_index']);
                    } catch (\Exception $e) {
                        // Index might not exist, continue
                    }
                }
            }
            
            // Drop columns
            $allColumns = ['username', 'radius_password', 'service_type', 'connection_type', 'billing_type', 'device_type', 
                          'mac_address', 'ip_address', 'status', 'expiry_date', 'zone_id'];
            
            foreach ($allColumns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
