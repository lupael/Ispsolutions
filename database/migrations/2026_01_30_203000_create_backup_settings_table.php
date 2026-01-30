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
        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operator_id')->index();
            $table->unsignedBigInteger('nas_id')->index();
            $table->string('primary_authenticator')->default('Radius');
            $table->timestamps();

            $table->unique(['operator_id']);
            $table->foreign('operator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('nas_id')->references('id')->on('nas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};
