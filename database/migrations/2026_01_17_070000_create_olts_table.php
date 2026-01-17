<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('ip_address', 45);
            $table->integer('port')->default(23);
            $table->enum('management_protocol', ['ssh', 'telnet', 'snmp'])->default('telnet');
            $table->string('username', 100);
            $table->string('password');
            $table->string('snmp_community')->nullable();
            $table->enum('snmp_version', ['v1', 'v2c', 'v3'])->nullable();
            $table->string('model', 100)->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamp('last_backup_at')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            $table->enum('health_status', ['ok', 'warning', 'error', 'unknown'])->default('unknown');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
