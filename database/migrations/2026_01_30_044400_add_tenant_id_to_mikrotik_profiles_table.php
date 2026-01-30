<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: tenant_id is intentionally added without a foreign key constraint 
     * to allow for flexible multi-tenancy scenarios where tenants table 
     * may not exist in all installations.
     */
    public function up(): void
    {
        Schema::table('mikrotik_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('router_id');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_profiles', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
