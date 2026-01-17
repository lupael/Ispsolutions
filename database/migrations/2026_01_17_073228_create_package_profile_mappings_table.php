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
        Schema::create('package_profile_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
            $table->string('profile_name');
            $table->string('speed_control_method')->default('router');
            $table->timestamps();
            
            $table->unique(['package_id', 'router_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_profile_mappings');
    }
};
