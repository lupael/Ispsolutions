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
        Schema::table('packages', function (Blueprint $table) {
            // Add visibility field (public/private) for package display settings
            if (! Schema::hasColumn('packages', 'visibility')) {
                $table->enum('visibility', ['public', 'private'])->default('public')->after('status');
            }
            
            // Add PPPoE profile association for direct profile mapping
            if (! Schema::hasColumn('packages', 'pppoe_profile_id')) {
                $table->foreignId('pppoe_profile_id')->nullable()->after('visibility')->constrained('mikrotik_profiles')->onDelete('set null');
                $table->index('pppoe_profile_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'pppoe_profile_id')) {
                $table->dropForeign(['pppoe_profile_id']);
                $table->dropIndex(['pppoe_profile_id']);
                $table->dropColumn('pppoe_profile_id');
            }
            
            if (Schema::hasColumn('packages', 'visibility')) {
                $table->dropColumn('visibility');
            }
        });
    }
};
