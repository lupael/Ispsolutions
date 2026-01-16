<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('service_package_id')->nullable()->after('remember_token')->constrained('service_packages')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('service_package_id');
            $table->timestamp('activated_at')->nullable()->after('is_active');
            
            $table->index('service_package_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['service_package_id']);
            $table->dropIndex(['service_package_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['service_package_id', 'is_active', 'activated_at']);
        });
    }
};
