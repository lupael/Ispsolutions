<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Task 3.3: Add database index for performance
     * Add composite index on (payment_type, status) for overall status filtering
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add composite index for faster overall_status queries
            if (!Schema::hasColumn('users', 'payment_type') || !Schema::hasColumn('users', 'status')) {
                // Columns don't exist yet, skip
                return;
            }
            
            // Check if index already exists
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('users');
            
            $indexExists = false;
            foreach ($indexes as $index) {
                if ($index->getName() === 'idx_user_overall_status') {
                    $indexExists = true;
                    break;
                }
            }
            
            if (!$indexExists) {
                $table->index(['payment_type', 'status'], 'idx_user_overall_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('users');
            
            foreach ($indexes as $index) {
                if ($index->getName() === 'idx_user_overall_status') {
                    $table->dropIndex('idx_user_overall_status');
                    break;
                }
            }
        });
    }
};
