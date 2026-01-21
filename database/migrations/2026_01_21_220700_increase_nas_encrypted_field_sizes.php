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
        Schema::table('nas', function (Blueprint $table) {
            // Increase size for encrypted fields (Laravel's encrypted cast creates ~200+ char JSON strings)
            $table->string('secret', 255)->change();
            $table->string('community', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nas', function (Blueprint $table) {
            // Revert to original sizes
            $table->string('secret', 100)->change();
            $table->string('community', 100)->nullable()->change();
        });
    }
};
