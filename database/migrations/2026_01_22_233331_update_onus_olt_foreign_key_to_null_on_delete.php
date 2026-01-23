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
            // Make olt_id nullable first
            $table->foreignId('olt_id')->nullable()->change();

            // Drop the existing foreign key
            $table->dropForeign(['olt_id']);

            // Re-add with nullOnDelete instead of cascade
            $table->foreign('olt_id')
                ->references('id')
                ->on('olts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onus', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['olt_id']);

            // Make olt_id not nullable
            $table->foreignId('olt_id')->nullable(false)->change();

            // Re-add with cascade
            $table->foreign('olt_id')
                ->references('id')
                ->on('olts')
                ->onDelete('cascade');
        });
    }
};
