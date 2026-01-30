<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds is_subscriber flag to users table to identify customers/subscribers.
     * Customers are external subscribers (Internet/PPP/Hotspot/CableTV) and are
     * not part of the administrative hierarchy (Levels 0-80).
     * 
     * IMPORTANT: This migration should be run with the application in maintenance mode
     * to prevent inconsistent state during the data migration.
     */
    public function up(): void
    {
        // First, make operator_level nullable to support customers with no level
        Schema::table('users', function (Blueprint $table) {
            $table->integer('operator_level')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            // Add is_subscriber boolean column
            if (!Schema::hasColumn('users', 'is_subscriber')) {
                $table->boolean('is_subscriber')->default(false)->after('operator_level');
                $table->index('is_subscriber');
            }
        });

        // Migrate existing customers (operator_level = 100) to is_subscriber = true
        // and set their operator_level to null as they're not in the hierarchy
        // Use transaction to ensure atomicity
        DB::transaction(function () {
            DB::table('users')
                ->where('operator_level', 100)
                ->update([
                    'is_subscriber' => true,
                    'operator_level' => null,
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert subscribers back to operator_level = 100
        DB::transaction(function () {
            DB::table('users')
                ->where('is_subscriber', true)
                ->update([
                    'operator_level' => 100,
                    'is_subscriber' => false,
                ]);
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_subscriber')) {
                try {
                    $table->dropIndex(['is_subscriber']);
                } catch (\Exception $e) {
                    // Index might not exist or have a different name
                }
                $table->dropColumn('is_subscriber');
            }
        });

        // Revert operator_level to non-nullable
        Schema::table('users', function (Blueprint $table) {
            $table->integer('operator_level')->default(100)->change();
        });
    }
};
