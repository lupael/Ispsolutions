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
        Schema::create('customer_time_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('daily_minutes_limit')->nullable(); // Daily time limit in minutes
            $table->unsignedInteger('monthly_minutes_limit')->nullable(); // Monthly time limit in minutes
            $table->unsignedInteger('session_duration_limit')->nullable(); // Max session duration in minutes
            $table->unsignedInteger('current_day_minutes')->default(0);
            $table->unsignedInteger('current_month_minutes')->default(0);
            $table->time('allowed_start_time')->nullable(); // e.g., 08:00
            $table->time('allowed_end_time')->nullable(); // e.g., 22:00
            $table->boolean('auto_disconnect_on_limit')->default(true);
            $table->date('day_reset_date')->nullable();
            $table->date('month_reset_date')->nullable();
            $table->timestamps();
            
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_time_limits');
    }
};
