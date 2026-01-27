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
        Schema::table('customer_imports', function (Blueprint $table) {
            // Make nas_id nullable since we might use router_id instead
            $table->foreignId('nas_id')->nullable()->change();
            
            // Add router_id as optional alternative to nas_id
            $table->foreignId('router_id')->nullable()->after('nas_id')
                ->constrained('mikrotik_routers')->nullOnDelete();
            
            $table->index('router_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_imports', function (Blueprint $table) {
            $table->dropForeign(['router_id']);
            $table->dropColumn('router_id');
            
            // Restore nas_id to non-nullable
            $table->foreignId('nas_id')->nullable(false)->change();
        });
    }
};
