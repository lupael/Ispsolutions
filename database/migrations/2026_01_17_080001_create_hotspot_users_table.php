<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('phone_number')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, active, suspended, expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_users');
    }
};
