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
            // Add NAS relationship
            $table->foreignId('nas_id')->nullable()->after('tenant_id')
                ->constrained('nas')->nullOnDelete();

            // Add RADIUS secret (encrypted)
            $table->string('radius_secret', 255)->nullable()->after('password');

            // Add public IP address
            $table->string('public_ip', 45)->nullable()->after('ip_address');

            // Add primary authentication mode
            $table->enum('primary_auth', ['radius', 'router', 'hybrid'])
                ->default('hybrid')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotik_routers', function (Blueprint $table) {
            $table->dropForeign(['nas_id']);
            $table->dropColumn(['nas_id', 'radius_secret', 'public_ip', 'primary_auth']);
        });
    }
};
