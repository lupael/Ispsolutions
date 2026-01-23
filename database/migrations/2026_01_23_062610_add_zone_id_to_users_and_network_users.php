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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('tenant_id')
                ->constrained()->nullOnDelete()
                ->comment('Geographic zone assignment');
        });

        Schema::table('network_users', function (Blueprint $table) {
            $table->foreignId('zone_id')->nullable()->after('tenant_id')
                ->constrained()->nullOnDelete()
                ->comment('Geographic zone assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });

        Schema::table('network_users', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropColumn('zone_id');
        });
    }
};
