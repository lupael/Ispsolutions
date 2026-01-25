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
        Schema::table('network_users', function (Blueprint $table) {
            // Add expiry_date column after status
            if (!Schema::hasColumn('network_users', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('status');
                $table->index('expiry_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_users', function (Blueprint $table) {
            if (Schema::hasColumn('network_users', 'expiry_date')) {
                $table->dropIndex(['expiry_date']);
                $table->dropColumn('expiry_date');
            }
        });
    }
};
