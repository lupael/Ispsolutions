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
        Schema::create('operator_registry', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('realm')->unique()->index();
            $table->string('portal_url');
            $table->string('radius_server')->nullable();
            $table->integer('radius_port')->nullable()->default(1812);
            $table->string('radius_secret')->nullable();
            $table->text('description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('country', 2)->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_registry');
    }
};
