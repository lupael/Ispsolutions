<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radius_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('username');
            $table->string('nas_ip_address', 45);
            $table->string('nas_port')->nullable();
            $table->string('acct_session_id')->unique();
            $table->timestamp('acct_start_time')->nullable();
            $table->timestamp('acct_stop_time')->nullable();
            $table->unsignedBigInteger('acct_session_time')->nullable()->comment('Session time in seconds');
            $table->unsignedBigInteger('acct_input_octets')->nullable()->comment('Downloaded bytes');
            $table->unsignedBigInteger('acct_output_octets')->nullable()->comment('Uploaded bytes');
            $table->string('acct_terminate_cause')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('username');
            $table->index('acct_start_time');
            $table->index('acct_stop_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radius_sessions');
    }
};
