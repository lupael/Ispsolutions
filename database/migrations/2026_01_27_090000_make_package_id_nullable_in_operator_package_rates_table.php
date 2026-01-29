<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes package_id nullable in operator_package_rates table.
     * This field is a legacy field maintained for backward compatibility.
     * New records use master_package_id instead.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY column syntax
        // We need to check the database driver
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // For SQLite, we need to recreate the table
            // Skip for SQLite tests as it's more complex
            return;
        }
        
        // Drop the foreign key constraint temporarily
        Schema::table('operator_package_rates', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        // Make package_id nullable using raw SQL (avoids needing doctrine/dbal)
        DB::statement('ALTER TABLE `operator_package_rates` MODIFY `package_id` BIGINT UNSIGNED NULL');

        // Re-add foreign key constraint with nullable support and add new unique constraint
        Schema::table('operator_package_rates', function (Blueprint $table) {
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('cascade');
            
            // Add unique constraint for master package based records (new system)
            // The existing unique_tenant_operator_package constraint remains for legacy records
            // MySQL UNIQUE constraints allow multiple NULLs, so both constraints can coexist
            $table->unique(['tenant_id', 'operator_id', 'master_package_id'], 'unique_tenant_operator_master_package');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support MODIFY column syntax
        // We need to check the database driver
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // Skip for SQLite tests
            return;
        }
        
        // Check if there are any NULL package_id rows
        $nullCount = DB::table('operator_package_rates')
            ->whereNull('package_id')
            ->count();

        if ($nullCount > 0) {
            throw new \RuntimeException(
                "Cannot rollback: {$nullCount} row(s) with NULL package_id exist. " .
                "Delete or update these rows before rolling back this migration."
            );
        }

        // Drop constraints
        Schema::table('operator_package_rates', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropUnique('unique_tenant_operator_master_package');
        });

        // Revert package_id to non-nullable using raw SQL
        DB::statement('ALTER TABLE `operator_package_rates` MODIFY `package_id` BIGINT UNSIGNED NOT NULL');

        // Re-add foreign key constraint
        Schema::table('operator_package_rates', function (Blueprint $table) {
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('cascade');
        });
    }
};
