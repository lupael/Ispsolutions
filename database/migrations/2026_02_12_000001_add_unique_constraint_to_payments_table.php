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
        Schema::table('payments', function (Blueprint $table) {
            $table->unique(['payment_gateway_id', 'transaction_id'], 'gateway_transaction_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('gateway_transaction_unique');
        });
    }
};
