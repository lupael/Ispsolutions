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
        Schema::table('packages', function (Blueprint $table) {
            // Add foreign key relationships for 3-tier hierarchy
            $table->foreignId('master_package_id')->nullable()->after('tenant_id')->constrained('master_packages')->onDelete('set null');
            $table->foreignId('operator_package_rate_id')->nullable()->after('master_package_id')->constrained('operator_package_rates')->onDelete('set null');
            
            // Add indexes
            $table->index('master_package_id');
            $table->index('operator_package_rate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['master_package_id']);
            $table->dropForeign(['operator_package_rate_id']);
            $table->dropIndex(['master_package_id']);
            $table->dropIndex(['operator_package_rate_id']);
            $table->dropColumn(['master_package_id', 'operator_package_rate_id']);
        });
    }
};
