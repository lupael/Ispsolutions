<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            if (! Schema::hasColumn('olts', 'port')) {
                $table->integer('port')->default(22)->after('ip_address');
            }

            if (! Schema::hasColumn('olts', 'snmp_port')) {
                $table->integer('snmp_port')->nullable()->default(161)->after('port');
            }

            if (! Schema::hasColumn('olts', 'management_protocol')) {
                $table->string('management_protocol', 20)->default('ssh')->after('port');
            }
        });
    }

    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            if (Schema::hasColumn('olts', 'snmp_port')) {
                $table->dropColumn('snmp_port');
            }
            // Keep 'port' and 'management_protocol' if they exist in prior migrations to avoid accidental drops.
        });
    }
};