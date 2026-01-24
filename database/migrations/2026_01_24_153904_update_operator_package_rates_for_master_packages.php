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
        Schema::table('operator_package_rates', function (Blueprint $table) {
            // Add new fields for 3-tier hierarchy
            $table->foreignId('master_package_id')->nullable()->after('package_id')->constrained('master_packages')->onDelete('cascade');
            $table->decimal('operator_price', 10, 2)->nullable()->after('custom_price');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('commission_percentage');
            $table->foreignId('assigned_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            
            // Add indexes
            $table->index('master_package_id');
            $table->index('status');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operator_package_rates', function (Blueprint $table) {
            $table->dropForeign(['master_package_id']);
            $table->dropForeign(['assigned_by']);
            $table->dropIndex(['master_package_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['assigned_by']);
            $table->dropColumn(['master_package_id', 'operator_price', 'status', 'assigned_by']);
        });
    }
};
