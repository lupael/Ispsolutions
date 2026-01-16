<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mikrotik_routers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('ip_address', 45);
            $table->integer('api_port')->default(8728);
            $table->string('username', 100);
            $table->string('password');
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->timestamps();
            
            $table->index('status');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mikrotik_routers');
    }
};
