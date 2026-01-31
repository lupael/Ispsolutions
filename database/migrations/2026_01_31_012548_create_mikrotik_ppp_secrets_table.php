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
        Schema::create('mikrotik_ppp_secrets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_import_id')->nullable()->constrained('customer_imports')->onDelete('set null');
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('nas_id')->constrained()->onDelete('cascade');
            $table->foreignId('router_id')->constrained('mikrotik_routers')->onDelete('cascade');
            
            // PPP Secret fields from MikroTik router
            $table->string('name')->index();  // Username
            $table->text('password');  // Encrypted password
            $table->string('profile')->nullable();  // PPP profile name
            $table->string('remote_address')->nullable();  // Static IP if configured
            $table->string('disabled', 3)->default('no');  // 'yes' or 'no'
            $table->text('comment')->nullable();  // Customer metadata from router
            
            $table->timestamps();
            
            // Indexes
            $table->index('tenant_id');
            $table->index('router_id');
            $table->index('nas_id');
            $table->index('customer_import_id');
            $table->index(['name', 'router_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mikrotik_ppp_secrets');
    }
};
