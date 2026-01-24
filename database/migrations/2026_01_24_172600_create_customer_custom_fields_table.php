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
        Schema::create('customer_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Field name (e.g., 'nid', 'passport')
            $table->string('label'); // Display label (e.g., 'National ID', 'Passport Number')
            $table->enum('type', ['text', 'number', 'date', 'select', 'checkbox', 'textarea']);
            $table->boolean('required')->default(false);
            $table->json('options')->nullable(); // For select/checkbox options
            $table->integer('order')->default(0);
            $table->json('visibility')->nullable(); // Which roles can see this field
            $table->string('category')->nullable(); // Group fields by category
            $table->timestamps();
            
            $table->index(['tenant_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_custom_fields');
    }
};
