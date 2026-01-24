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
                $table->integer('forwarding_port')->nullable()->after('is_active');
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
