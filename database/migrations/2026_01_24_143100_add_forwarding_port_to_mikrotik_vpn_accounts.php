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
        Schema::table('mikrotik_vpn_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('mikrotik_vpn_accounts', 'forwarding_port')) {
                // Add column at the end if is_active doesn't exist
                $hasIsActive = Schema::hasColumn('mikrotik_vpn_accounts', 'is_active');
                if ($hasIsActive) {
                    $table->integer('forwarding_port')->nullable()->after('is_active');
                } else {
                    $table->integer('forwarding_port')->nullable();
                }
                $table->index('forwarding_port');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_vpn_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('mikrotik_vpn_accounts', 'forwarding_port')) {
                $table->dropColumn('forwarding_port');
            }
        });
    }
};
