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
        Schema::create('vpn_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('network'); // e.g., 10.10.10.0
            $table->string('subnet_mask', 15); // e.g., 255.255.255.0 (dotted decimal notation)
            $table->ipAddress('start_ip'); // Start of IP range
            $table->ipAddress('end_ip'); // End of IP range
            $table->ipAddress('gateway')->nullable();
            $table->ipAddress('dns_primary')->nullable();
            $table->ipAddress('dns_secondary')->nullable();
            $table->enum('protocol', ['pptp', 'l2tp', 'openvpn', 'ikev2', 'wireguard'])->default('pptp');
            $table->unsignedInteger('total_ips')->default(0);
            $table->unsignedInteger('used_ips')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_active']);
            $table->index('protocol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vpn_pools');
    }
};
