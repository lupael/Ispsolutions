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
        Schema::table('network_users', function (Blueprint $table) {
            // Add connection_type column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'connection_type')) {
                $table->enum('connection_type', ['pppoe', 'hotspot', 'static', 'dhcp', 'vpn'])->nullable()->after('expiry_date');
            }

            // Add billing_type column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'billing_type')) {
                $table->enum('billing_type', ['prepaid', 'postpaid', 'unlimited'])->nullable()->after('connection_type');
            }

            // Add device_type column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'device_type')) {
                $table->string('device_type', 100)->nullable()->after('billing_type');
            }

            // Add mac_address column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'mac_address')) {
                $table->string('mac_address', 17)->nullable()->after('device_type');
                $table->index('mac_address');
            }

            // Add ip_address column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('mac_address');
                $table->index('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_users', function (Blueprint $table) {
            if (Schema::hasColumn('network_users', 'connection_type')) {
                $table->dropColumn('connection_type');
            }

            if (Schema::hasColumn('network_users', 'billing_type')) {
                $table->dropColumn('billing_type');
            }

            if (Schema::hasColumn('network_users', 'device_type')) {
                $table->dropColumn('device_type');
            }

            if (Schema::hasColumn('network_users', 'mac_address')) {
                $table->dropIndex(['mac_address']);
                $table->dropColumn('mac_address');
            }

            if (Schema::hasColumn('network_users', 'ip_address')) {
                $table->dropIndex(['ip_address']);
                $table->dropColumn('ip_address');
            }
        });
    }
};
