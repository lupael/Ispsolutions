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
        Schema::create('mikrotik_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('target');
            $table->string('parent')->nullable();
            $table->string('max_limit')->nullable();
            $table->string('burst_limit')->nullable();
            $table->string('burst_threshold')->nullable();
            $table->integer('burst_time')->nullable();
            $table->integer('priority')->default(8);
            $table->timestamps();
            
            $table->unique(['router_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_queues');
    }
};
