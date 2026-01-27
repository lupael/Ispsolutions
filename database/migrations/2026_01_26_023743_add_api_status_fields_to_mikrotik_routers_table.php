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
            // Add api_status column if it doesn't exist
            if (!Schema::hasColumn('mikrotik_routers', 'api_status')) {
                $table->enum('api_status', ['online', 'offline', 'warning', 'unknown'])->default('unknown')->after('password');
            }
            
            // Add last_checked_at column if it doesn't exist
            if (!Schema::hasColumn('mikrotik_routers', 'last_checked_at')) {
                $table->timestamp('last_checked_at')->nullable()->after('api_status');
            }
            
            // Add last_error column if it doesn't exist
            if (!Schema::hasColumn('mikrotik_routers', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_checked_at');
            }
            
            // Add response_time_ms column if it doesn't exist
            if (!Schema::hasColumn('mikrotik_routers', 'response_time_ms')) {
                $table->integer('response_time_ms')->nullable()->after('last_error')->comment('API response time in milliseconds');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            $table->dropColumn(['api_status', 'last_checked_at', 'last_error', 'response_time_ms']);
        });
    }
};
