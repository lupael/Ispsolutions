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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('operator_level')->default(100)->after('is_active');
            $table->json('disabled_menus')->nullable()->after('operator_level');
            $table->foreignId('manager_id')->nullable()->after('disabled_menus')->constrained('users')->nullOnDelete();
            $table->string('operator_type')->nullable()->after('manager_id'); // super_admin, group_admin, operator, sub_operator, manager, card_distributor, developer, accountant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['operator_level', 'disabled_menus', 'manager_id', 'operator_type']);
        });
    }
};
