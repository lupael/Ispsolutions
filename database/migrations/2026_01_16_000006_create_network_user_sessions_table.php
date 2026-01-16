<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('network_users')->onDelete('cascade');
            $table->string('session_id', 100)->unique();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->bigInteger('upload_bytes')->default(0);
            $table->bigInteger('download_bytes')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->string('nas_ip', 45)->nullable();
            $table->enum('status', ['active', 'terminated'])->default('active');
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
            $table->index('status');
            $table->index('start_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_user_sessions');
    }
};
