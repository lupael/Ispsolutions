<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes custom_price nullable in operator_package_rates table.
     * This field is a legacy field maintained for backward compatibility.
     * New records use operator_price instead.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY column syntax
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // Skip for SQLite tests
            return;
        }
        
        // Make custom_price nullable using raw SQL (avoids needing doctrine/dbal)
        DB::statement('ALTER TABLE `operator_package_rates` MODIFY `custom_price` DECIMAL(10, 2) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support MODIFY column syntax
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // Skip for SQLite tests
            return;
        }
        
        // Check if there are any NULL custom_price rows
        $nullCount = DB::table('operator_package_rates')
            ->whereNull('custom_price')
            ->count();

        if ($nullCount > 0) {
            throw new \RuntimeException(
                "Cannot rollback: {$nullCount} row(s) with NULL custom_price exist. " .
                "Delete or update these rows before rolling back this migration."
            );
        }

        // Revert custom_price to non-nullable using raw SQL
        DB::statement('ALTER TABLE `operator_package_rates` MODIFY `custom_price` DECIMAL(10, 2) NOT NULL');
    }
};
