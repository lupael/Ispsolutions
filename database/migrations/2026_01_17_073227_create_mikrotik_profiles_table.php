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
        Schema::create('mikrotik_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('local_address')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('rate_limit')->nullable();
            $table->integer('session_timeout')->nullable();
            $table->integer('idle_timeout')->nullable();
            $table->timestamps();
            
            $table->unique(['router_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_profiles');
    }
};
