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
        Schema::table('master_packages', function (Blueprint $table) {
            $table->foreignId('pppoe_profile_id')->nullable()->after('status')->constrained('mikrotik_profiles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_packages', function (Blueprint $table) {
            $table->dropForeign(['pppoe_profile_id']);
            $table->dropColumn('pppoe_profile_id');
        });
    }
};
