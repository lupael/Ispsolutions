<?php

use Illuminate-support-facades-schema;
use Illuminate-database-schema-blueprint;
use Illuminate-database-migrations-migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['user_id', 'service_package_id', 'billing_period_start'], 'user_package_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('user_package_period_unique');
        });
    }
};
