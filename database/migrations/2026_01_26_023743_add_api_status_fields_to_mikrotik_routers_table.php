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
            $table->enum('api_status', ['online', 'offline', 'warning', 'unknown'])->default('unknown')->after('api_password');
            $table->timestamp('last_checked_at')->nullable()->after('api_status');
            $table->text('last_error')->nullable()->after('last_checked_at');
            $table->integer('response_time_ms')->nullable()->after('last_error')->comment('API response time in milliseconds');
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
