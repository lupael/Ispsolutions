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
        Schema::table('onus', function (Blueprint $table) {
            $table->string('model')->nullable()->after('serial_number');
            $table->string('hw_version')->nullable()->after('model');
            $table->string('sw_version')->nullable()->after('hw_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onus', function (Blueprint $table) {
            $table->dropColumn(['model', 'hw_version', 'sw_version']);
        });
    }
};
