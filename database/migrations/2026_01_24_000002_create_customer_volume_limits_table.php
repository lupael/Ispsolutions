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
        Schema::create('customer_volume_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('monthly_limit_mb')->nullable(); // Monthly data cap in MB
            $table->unsignedBigInteger('daily_limit_mb')->nullable(); // Daily data cap in MB
            $table->unsignedBigInteger('current_month_usage_mb')->default(0);
            $table->unsignedBigInteger('current_day_usage_mb')->default(0);
            $table->date('month_reset_date')->nullable();
            $table->date('day_reset_date')->nullable();
            $table->boolean('auto_suspend_on_limit')->default(true);
            $table->boolean('rollover_enabled')->default(false);
            $table->unsignedBigInteger('rollover_balance_mb')->default(0);
            $table->timestamps();
            
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_volume_limits');
    }
};
