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
        Schema::create('cable_tv_channel_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cable_tv_channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('cable_tv_package_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['cable_tv_channel_id', 'cable_tv_package_id'], 'channel_package_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cable_tv_channel_package');
    }
};
