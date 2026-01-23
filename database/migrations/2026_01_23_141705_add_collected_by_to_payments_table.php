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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('collected_by')->nullable()->after('user_id')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('The operator/staff who collected this payment');

            // Add explicit index for queries filtering/grouping by collected_by
            $table->index('collected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['collected_by']);
            $table->dropForeign(['collected_by']);
            $table->dropColumn('collected_by');
        });
    }
};
