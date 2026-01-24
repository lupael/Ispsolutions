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
        Schema::create('vat_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('name'); // e.g., Standard, Reduced, Zero
            $table->decimal('rate', 5, 2); // VAT rate percentage (e.g., 15.00, 5.00, 0.00)
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('tenant_id');
        });

        Schema::create('vat_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->cascadeOnDelete();
            $table->foreignId('vat_profile_id')->constrained('vat_profiles')->cascadeOnDelete();
            $table->decimal('base_amount', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->date('collection_date');
            $table->string('tax_period'); // e.g., 2026-01
            $table->timestamps();
            
            $table->index(['tax_period', 'vat_profile_id']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_collections');
        Schema::dropIfExists('vat_profiles');
    }
};
