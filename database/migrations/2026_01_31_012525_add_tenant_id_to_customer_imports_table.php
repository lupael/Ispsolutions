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
            UPDATE customer_imports ci
            INNER JOIN mikrotik_routers mr ON ci.router_id = mr.id
            SET ci.tenant_id = mr.tenant_id
            WHERE ci.tenant_id IS NULL AND ci.router_id IS NOT NULL
        ');
        
        // For any remaining NULL tenant_id rows (no router), try to infer from nas
        DB::statement('
            UPDATE customer_imports ci
            INNER JOIN nas n ON ci.nas_id = n.id
            SET ci.tenant_id = n.tenant_id
            WHERE ci.tenant_id IS NULL AND ci.nas_id IS NOT NULL
        ');
        
        // For any remaining NULL tenant_id rows (no router or nas), try operator
        DB::statement('
            UPDATE customer_imports ci
            INNER JOIN users u ON ci.operator_id = u.id
            SET ci.tenant_id = u.tenant_id
            WHERE ci.tenant_id IS NULL AND ci.operator_id IS NOT NULL
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
