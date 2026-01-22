<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This table stores custom role labels that Admins can configure.
     * Allows renaming "Operator" to "Partner", "Agent", etc. without breaking role logic.
     */
    public function up(): void
    {
        Schema::create('role_label_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('role_slug'); // e.g., 'operator', 'sub-operator'
            $table->string('custom_label'); // e.g., 'Partner', 'Agent', 'Sub-Partner'
            $table->timestamps();

            $table->unique(['tenant_id', 'role_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_label_settings');
    }
};
