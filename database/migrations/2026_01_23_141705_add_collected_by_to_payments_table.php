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
            // Note: Foreign key automatically creates an index, so no need for explicit index()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['collected_by']);
            $table->dropColumn('collected_by');
        });
    }
};
