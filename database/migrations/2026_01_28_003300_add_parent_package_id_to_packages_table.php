<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Task 8.1: Add parent_package_id to packages table
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'parent_package_id')) {
                $table->foreignId('parent_package_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('packages')
                    ->nullOnDelete();
                $table->index('parent_package_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'parent_package_id')) {
                $table->dropForeign(['parent_package_id']);
                $table->dropIndex(['parent_package_id']);
                $table->dropColumn('parent_package_id');
            }
        });
    }
};
