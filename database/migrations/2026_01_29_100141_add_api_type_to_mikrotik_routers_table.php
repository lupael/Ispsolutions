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
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            // Add api_type field to support both binary (v6/v7) and REST (v7+) APIs
            // Default to 'auto' for automatic detection, fallback to binary for v6 compatibility
            $table->enum('api_type', ['auto', 'binary', 'rest'])
                ->default('auto')
                ->after('api_port')
                ->comment('API type: auto (detect), binary (v6/v7 port 8728), rest (v7+ HTTP)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            $table->dropColumn('api_type');
        });
    }
};
