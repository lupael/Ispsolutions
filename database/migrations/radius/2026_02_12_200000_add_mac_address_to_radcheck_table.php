<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('radius')->table('radcheck', function (Blueprint $table) {
            $table->string('mac_address')->nullable()->after('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('radius')->table('radcheck', function (Blueprint $table) {
            $table->dropColumn('mac_address');
        });
    }
};
