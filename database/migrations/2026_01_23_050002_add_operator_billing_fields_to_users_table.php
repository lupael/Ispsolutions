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
            if (! Schema::hasColumn('users', 'billing_cycle')) {
                $table->string('billing_cycle', 50)->default('monthly')->after('email');
            }
            if (! Schema::hasColumn('users', 'billing_day_of_month')) {
                $table->integer('billing_day_of_month')->default(1)->after('billing_cycle');
            }
            if (! Schema::hasColumn('users', 'payment_type')) {
                $table->enum('payment_type', ['prepaid', 'postpaid'])->default('postpaid')->after('billing_day_of_month');
            }
            if (! Schema::hasColumn('users', 'wallet_balance')) {
                $table->decimal('wallet_balance', 10, 2)->default(0)->after('payment_type');
            }
            if (! Schema::hasColumn('users', 'sms_balance')) {
                $table->integer('sms_balance')->default(0)->after('wallet_balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'billing_cycle')) {
                $table->dropColumn('billing_cycle');
            }
            if (Schema::hasColumn('users', 'billing_day_of_month')) {
                $table->dropColumn('billing_day_of_month');
            }
            if (Schema::hasColumn('users', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
            if (Schema::hasColumn('users', 'wallet_balance')) {
                $table->dropColumn('wallet_balance');
            }
            if (Schema::hasColumn('users', 'sms_balance')) {
                $table->dropColumn('sms_balance');
            }
        });
    }
};
