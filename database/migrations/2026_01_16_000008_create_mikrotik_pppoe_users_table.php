<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_pppoe_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->onDelete('cascade');
            $table->string('username', 100);
            $table->string('password');
            $table->string('service', 50)->default('pppoe');
            $table->string('profile', 100)->nullable();
            $table->string('local_address', 45)->nullable();
            $table->string('remote_address', 45)->nullable();
            $table->enum('status', ['active', 'inactive', 'synced'])->default('active');
            $table->timestamps();
            
            $table->index('router_id');
            $table->index('username');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_pppoe_users');
    }
};
