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
            // Add operator form fields
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'company_address')) {
                $table->text('company_address')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('users', 'company_phone')) {
                $table->string('company_phone', 20)->nullable()->after('company_address');
            }
            if (!Schema::hasColumn('users', 'credit_limit')) {
                $table->decimal('credit_limit', 10, 2)->default(0)->after('wallet_balance');
            }
            if (!Schema::hasColumn('users', 'allow_sub_operator')) {
                $table->boolean('allow_sub_operator')->default(true)->after('operator_type');
            }
            if (!Schema::hasColumn('users', 'allow_rename_package')) {
                $table->boolean('allow_rename_package')->default(false)->after('allow_sub_operator');
            }
            if (!Schema::hasColumn('users', 'sms_charges_by')) {
                $table->enum('sms_charges_by', ['admin', 'operator'])->default('admin')->after('sms_balance');
            }
            if (!Schema::hasColumn('users', 'sms_cost_per_unit')) {
                $table->decimal('sms_cost_per_unit', 8, 4)->default(0)->after('sms_charges_by');
            }
            if (!Schema::hasColumn('users', 'can_manage_customers')) {
                $table->boolean('can_manage_customers')->default(true)->after('allow_rename_package');
            }
            if (!Schema::hasColumn('users', 'can_view_financials')) {
                $table->boolean('can_view_financials')->default(true)->after('can_manage_customers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'company_name',
                'company_address', 
                'company_phone',
                'credit_limit',
                'allow_sub_operator',
                'allow_rename_package',
                'sms_charges_by',
                'sms_cost_per_unit',
                'can_manage_customers',
                'can_view_financials'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
