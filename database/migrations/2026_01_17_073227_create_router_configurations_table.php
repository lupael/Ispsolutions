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
        Schema::create('router_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
            $table->string('config_type')->index();
            $table->json('config_data');
            $table->timestamp('applied_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            
            $table->index(['router_id', 'config_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('router_configurations');
    }
};
