<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add network_user_id column if it doesn't exist
            if (!Schema::hasColumn('invoices', 'network_user_id')) {
                $table->foreignId('network_user_id')->nullable()->after('user_id')->constrained('network_users')->nullOnDelete();
                $table->index('network_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'network_user_id')) {
                $table->dropForeign(['network_user_id']);
                $table->dropIndex(['network_user_id']);
                $table->dropColumn('network_user_id');
            }
        });
    }
};
