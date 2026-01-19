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
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug'); // twilio, nexmo, msg91, bulksms, custom
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->text('configuration')->nullable(); // Encrypted JSON field for API keys, secrets, sender_id, etc.
            $table->decimal('balance', 10, 2)->default(0); // SMS balance
            $table->decimal('rate_per_sms', 8, 4)->default(0); // Cost per SMS
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
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
