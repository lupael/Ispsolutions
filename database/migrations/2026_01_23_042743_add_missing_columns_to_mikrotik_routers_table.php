<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            // Add host column if it doesn't exist
            if (! Schema::hasColumn('mikrotik_routers', 'host')) {
                $table->string('host', 255)->nullable()->after('ip_address');
            }

            // Add tenant_id column if it doesn't exist
            if (! Schema::hasColumn('mikrotik_routers', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->index('tenant_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            if (Schema::hasColumn('mikrotik_routers', 'host')) {
                $table->dropColumn('host');
            }

            if (Schema::hasColumn('mikrotik_routers', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
