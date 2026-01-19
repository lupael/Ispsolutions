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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->json('features')->nullable(); // Store features as JSON array
            $table->unsignedInteger('max_users')->nullable(); // Max users allowed, null = unlimited
            $table->unsignedInteger('max_routers')->nullable(); // Max routers allowed
            $table->unsignedInteger('max_olts')->nullable(); // Max OLTs allowed
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('trial_days')->default(0); // Trial period in days
            $table->unsignedInteger('sort_order')->default(0); // For ordering plans
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('slug');
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
