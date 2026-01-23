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
        // Add tenant_id to operator_package_rates for proper tenant isolation
        if (!Schema::hasColumn('operator_package_rates', 'tenant_id')) {
            Schema::table('operator_package_rates', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index('tenant_id');
            });
            
            // Drop old unique constraint and add new one with tenant_id
            Schema::table('operator_package_rates', function (Blueprint $table) {
                $table->dropUnique('unique_operator_package');
                $table->unique(['tenant_id', 'operator_id', 'package_id'], 'unique_tenant_operator_package');
            });
        }

        // Add tenant_id to operator_wallet_transactions for proper tenant isolation
        if (!Schema::hasColumn('operator_wallet_transactions', 'tenant_id')) {
            Schema::table('operator_wallet_transactions', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index('tenant_id');
            });
        }

        // Add tenant_id and unique constraint to operator_sms_rates
        if (!Schema::hasColumn('operator_sms_rates', 'tenant_id')) {
            Schema::table('operator_sms_rates', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id', 'operator_id']);
                $table->unique('operator_id', 'unique_operator_sms_rate');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('operator_package_rates', 'tenant_id')) {
            Schema::table('operator_package_rates', function (Blueprint $table) {
                $table->dropUnique('unique_tenant_operator_package');
                $table->unique(['operator_id', 'package_id'], 'unique_operator_package');
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('operator_wallet_transactions', 'tenant_id')) {
            Schema::table('operator_wallet_transactions', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasColumn('operator_sms_rates', 'tenant_id')) {
            Schema::table('operator_sms_rates', function (Blueprint $table) {
                $table->dropUnique('unique_operator_sms_rate');
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
