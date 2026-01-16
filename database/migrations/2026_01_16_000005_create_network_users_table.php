<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('password');
            $table->enum('service_type', ['pppoe', 'hotspot', 'static'])->default('pppoe');
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamps();
            
            $table->index('username');
            $table->index('service_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_users');
    }
};
