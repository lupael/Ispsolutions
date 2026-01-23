<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('network_users', function (Blueprint $table) {
            // Add is_active column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
            
            // Add tenant_id column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
                $table->index('tenant_id');
            }
            
            // Add email column if it doesn't exist
            if (!Schema::hasColumn('network_users', 'email')) {
                $table->string('email')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('network_users', function (Blueprint $table) {
            if (Schema::hasColumn('network_users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            
            if (Schema::hasColumn('network_users', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropIndex(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            
            if (Schema::hasColumn('network_users', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
