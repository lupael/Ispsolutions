<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drops the network_users table after data has been migrated to users table.
     * IMPORTANT: Run this migration only after verifying data migration was successful.
     */
    public function up(): void
    {
        // Drop the network_users table if it exists
        Schema::dropIfExists('network_users');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: Cannot restore the table structure.
     * If you need to rollback, restore from database backup.
     */
    public function down(): void
    {
        // Cannot recreate the table with all its data
        // Restore from backup if needed
    }
};
