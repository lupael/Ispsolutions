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
            if (!Schema::hasColumn('packages', 'operator_id')) {
                $table->foreignId('operator_id')->nullable()->after('tenant_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('packages', 'is_global')) {
                $table->boolean('is_global')->default(true)->after('operator_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'operator_id')) {
                $table->dropForeign(['operator_id']);
                $table->dropColumn('operator_id');
            }
            if (Schema::hasColumn('packages', 'is_global')) {
                $table->dropColumn('is_global');
            }
        });
    }
};
