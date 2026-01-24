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
        Schema::create('hotspot_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotspot_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('network_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('username')->nullable()->index();
            $table->string('mac_address', 50)->nullable()->index();
            $table->string('ip_address', 50)->nullable()->index();
            $table->string('session_id')->unique()->index();
            $table->string('login_type', 20)->default('normal')->index();
            $table->string('scenario', 50)->nullable();
            $table->timestamp('login_at')->nullable()->index();
            $table->timestamp('logout_at')->nullable()->index();
            $table->unsignedInteger('session_duration')->default(0);
            $table->text('device_fingerprint')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('nas_ip_address', 50)->nullable();
            $table->string('calling_station_id', 50)->nullable();
            
            // Link login fields (Scenario 8)
            $table->string('link_token', 100)->nullable()->unique()->index();
            $table->timestamp('link_expires_at')->nullable();
            $table->boolean('is_link_login')->default(false)->index();
            
            // Federated login fields (Scenario 10)
            $table->unsignedBigInteger('home_operator_id')->nullable()->index();
            $table->boolean('federated_login')->default(false)->index();
            $table->string('redirect_url')->nullable();
            
            $table->string('status', 20)->default('active')->index();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['tenant_id', 'status', 'login_at']);
            $table->index(['tenant_id', 'mac_address', 'status']);
            $table->index(['tenant_id', 'username', 'login_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_login_logs');
    }
};
