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
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider'); // e.g., 'twilio', 'nexmo', 'bulksms', 'custom'
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('sender_id')->nullable(); // Sender name or number
            $table->json('configuration')->nullable(); // Additional provider-specific settings
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('priority')->default(0); // For fallback ordering
            $table->decimal('cost_per_sms', 8, 4)->nullable(); // Cost tracking
            $table->unsignedInteger('messages_sent')->default(0); // Counter
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'priority']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};
