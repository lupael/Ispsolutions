<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_imports', function (Blueprint $table) {
            // Add tenant_id for multi-tenancy support (maps to mgid in IspBills)
            $table->foreignId('tenant_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            
            // Add index for tenant_id
            $table->index('tenant_id');
        });
        
        // Backfill tenant_id for existing rows from router relationship
        // This prevents existing imports from disappearing when BelongsToTenant scope is applied
        DB::statement('
            UPDATE customer_imports
            SET tenant_id = (SELECT tenant_id FROM mikrotik_routers WHERE id = customer_imports.router_id)
            WHERE tenant_id IS NULL AND router_id IS NOT NULL AND EXISTS (SELECT 1 FROM mikrotik_routers WHERE id = customer_imports.router_id)
        ');
        
        // For any remaining NULL tenant_id rows (no router), try to infer from nas
        DB::statement('
            UPDATE customer_imports
            SET tenant_id = (SELECT tenant_id FROM nas WHERE id = customer_imports.nas_id)
            WHERE tenant_id IS NULL AND nas_id IS NOT NULL AND EXISTS (SELECT 1 FROM nas WHERE id = customer_imports.nas_id)
        ');
        
        // For any remaining NULL tenant_id rows (no router or nas), try operator
        DB::statement('
            UPDATE customer_imports
            SET tenant_id = (SELECT tenant_id FROM users WHERE id = customer_imports.operator_id)
            WHERE tenant_id IS NULL AND operator_id IS NOT NULL AND EXISTS (SELECT 1 FROM users WHERE id = customer_imports.operator_id)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_imports', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
