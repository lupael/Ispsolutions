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
        Schema::create('cisco_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('ip_address', 45);
            $table->string('device_type', 50)->default('router');
            $table->string('model', 100)->nullable();
            $table->string('ios_version', 50)->nullable();
            $table->string('ssh_username', 100)->nullable();
            $table->string('ssh_password')->nullable();
            $table->string('enable_password')->nullable();
            $table->integer('ssh_port')->default(22);
            $table->integer('telnet_port')->default(23);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cisco_devices');
    }
};
