<?php

declare(strict_types=1);

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
        Schema::table('network_users', function (Blueprint $table) {
            $table->boolean('fup_exceeded')->default(false)->after('status')
                ->comment('Indicates if user has exceeded FUP data limit');
            $table->timestamp('fup_exceeded_at')->nullable()->after('fup_exceeded')
                ->comment('Timestamp when FUP limit was exceeded');
            $table->timestamp('fup_reset_at')->nullable()->after('fup_exceeded_at')
                ->comment('Timestamp when FUP counter will be reset based on package reset period');
            
            // Add index for efficient querying of users with exceeded FUP
            $table->index(['fup_exceeded', 'fup_reset_at'], 'idx_fup_exceeded_reset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_users', function (Blueprint $table) {
            $table->dropIndex('idx_fup_exceeded_reset');
            $table->dropColumn(['fup_exceeded', 'fup_exceeded_at', 'fup_reset_at']);
        });
    }
};
