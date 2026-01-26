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
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('daily_rate', 10, 2)->nullable()->after('price')->comment('Daily rate for daily billing');
            $table->boolean('allow_partial_day')->default(false)->after('daily_rate')->comment('Allow charging for partial days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['daily_rate', 'allow_partial_day']);
        });
    }
};
