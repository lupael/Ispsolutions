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
        Schema::table('package_profile_mappings', function (Blueprint $table) {
            if (!Schema::hasColumn('package_profile_mappings', 'ip_pool_id')) {
                $table->foreignId('ip_pool_id')->nullable()->after('router_id')->constrained('ip_pools')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_profile_mappings', function (Blueprint $table) {
            if (Schema::hasColumn('package_profile_mappings', 'ip_pool_id')) {
                $table->dropForeign(['ip_pool_id']);
                $table->dropColumn('ip_pool_id');
            }
        });
    }
};
