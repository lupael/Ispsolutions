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
        Schema::table('mikrotik_profiles', function (Blueprint $table) {
            $table->foreignId('ipv4_pool_id')->nullable()->after('router_id')->constrained('ip_pools')->onDelete('set null');
            $table->foreignId('ipv6_pool_id')->nullable()->after('ipv4_pool_id')->constrained('ip_pools')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_profiles', function (Blueprint $table) {
            $table->dropForeign(['ipv4_pool_id']);
            $table->dropForeign(['ipv6_pool_id']);
            $table->dropColumn(['ipv4_pool_id', 'ipv6_pool_id']);
        });
    }
};
