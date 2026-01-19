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
            $table->string('key')->unique(); // The public API key identifier (stored as-is, validated via secret)
            $table->string('secret'); // Secret associated with the API key (hashed in application)
            $table->json('permissions')->nullable(); // JSON array of allowed permissions
            $table->json('ip_whitelist')->nullable(); // IP whitelist (single or multiple IPs)
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
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
