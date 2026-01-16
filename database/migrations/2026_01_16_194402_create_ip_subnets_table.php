<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_subnets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ip_pool_id')->constrained('ip_pools')->onDelete('cascade');
            $table->string('network', 45)->comment('Network address (IPv4 or IPv6)');
            $table->unsignedTinyInteger('prefix_length')->comment('CIDR prefix length');
            $table->string('gateway', 45)->nullable()->comment('Gateway IP address');
            $table->unsignedSmallInteger('vlan_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['network', 'prefix_length']);
            $table->index('ip_pool_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_subnets');
    }
};
