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
        Schema::create('sms_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('event_name'); // bill_generated, payment_received, package_expired, etc.
            $table->string('event_label'); // Human-readable label
            $table->text('message_template');
            $table->boolean('is_active')->default(true);
            $table->json('available_variables')->nullable(); // Variables that can be used in template
            $table->timestamps();
            
            $table->unique('event_name');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_events');
    }
};
