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
        Schema::table('olts', function (Blueprint $table) {
            // Add missing fields from the form that were not being saved
            $table->string('brand', 50)->nullable()->after('name');
            $table->string('firmware_version', 100)->nullable()->after('model');
            $table->integer('telnet_port')->nullable()->after('port');
            $table->string('coverage_area')->nullable()->after('location');
            $table->integer('total_ports')->nullable()->after('coverage_area');
            $table->integer('max_onus')->nullable()->after('total_ports');
            $table->integer('snmp_port')->nullable()->after('snmp_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn([
                'brand',
                'firmware_version',
                'telnet_port',
                'coverage_area',
                'total_ports',
                'max_onus',
                'snmp_port',
            ]);
        });
    }
};
