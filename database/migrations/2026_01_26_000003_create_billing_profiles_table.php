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
        Schema::create('billing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['daily', 'monthly', 'free'])->default('monthly');
            $table->integer('billing_day')->nullable()->comment('1-31 for monthly billing');
            $table->time('billing_time')->nullable()->comment('HH:MM for daily billing');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('currency', 3)->default('BDT');
            $table->boolean('auto_generate_bill')->default(true);
            $table->boolean('auto_suspend')->default(true);
            $table->integer('grace_period_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Add billing_profile_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('billing_profile_id')->nullable()->after('tenant_id')->constrained('billing_profiles')->nullOnDelete();
            $table->index('billing_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['billing_profile_id']);
            $table->dropIndex(['billing_profile_id']);
            $table->dropColumn('billing_profile_id');
        });
        
        Schema::dropIfExists('billing_profiles');
    }
};
