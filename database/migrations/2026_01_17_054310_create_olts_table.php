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
            $table->string('vendor', 50)->default('huawei');
            $table->string('model', 100)->nullable();
            $table->string('telnet_username', 100)->nullable();
            $table->string('telnet_password')->nullable();
            $table->string('snmp_community', 100)->nullable();
            $table->integer('telnet_port')->default(23);
            $table->integer('snmp_port')->default(161);
            $table->integer('max_onts')->default(0);
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
        Schema::dropIfExists('olts');
    }
};
