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
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('ip_address', 45);
            $table->integer('port')->default(23);
            $table->string('management_protocol', 20)->default('telnet');
            $table->string('username', 100)->nullable();
            $table->text('password')->nullable();
            $table->string('snmp_community', 500)->nullable(); // Increased size to accommodate encrypted data
            $table->string('snmp_version', 10)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('health_status', 20)->default('unknown');
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
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
        Schema::dropIfExists('olts');
    }
};
