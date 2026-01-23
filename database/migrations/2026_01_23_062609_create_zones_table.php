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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('zones')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 8)->nullable()->comment('Center latitude for radius coverage');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Center longitude for radius coverage');
            $table->decimal('radius', 10, 2)->nullable()->comment('Coverage radius in kilometers');
            $table->string('color', 7)->default('#3B82F6')->comment('Zone color for map visualization');
            $table->boolean('is_active')->default(true);
            $table->enum('coverage_type', ['point', 'radius', 'polygon'])->default('point')
                ->comment('Type of geographic coverage');
            $table->json('coverage_data')->nullable()->comment('Polygon coordinates or additional coverage data');
            $table->json('metadata')->nullable()->comment('Additional zone metadata');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
            $table->index('parent_id');

            // Zone code should be unique per tenant, not globally
            $table->unique(['tenant_id', 'code'], 'zones_tenant_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
