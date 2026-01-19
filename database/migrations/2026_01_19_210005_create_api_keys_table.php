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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name'); // Human-readable name for the key
            $table->string('key')->unique(); // The actual API key
            $table->text('permissions')->nullable(); // JSON array of allowed permissions
            $table->ipAddress('allowed_ip')->nullable(); // IP whitelist (single IP)
            $table->json('allowed_ips')->nullable(); // Multiple IPs whitelist
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->unsignedInteger('rate_limit')->default(1000); // Requests per hour
            $table->timestamps();
            
            $table->index('key');
            $table->index(['user_id', 'is_active']);
            $table->index(['tenant_id', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
