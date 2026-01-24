<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('router_configuration_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('template_type', [
                'radius',
                'hotspot',
                'pppoe',
                'firewall',
                'system',
                'nat',
                'walled_garden',
                'suspended_pool',
                'full_provisioning',
            ])->default('full_provisioning');
            $table->json('configuration');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('template_type');
            $table->index('is_active');
        });

        Schema::create('router_provisioning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('template_id')->nullable()->constrained('router_configuration_templates')->onDelete('set null');
            $table->enum('action', ['provision', 'rollback', 'validate', 'backup'])->default('provision');
            $table->enum('status', ['pending', 'in_progress', 'success', 'failed', 'rolled_back'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('configuration')->nullable();
            $table->json('steps')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('router_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('action');
        });

        Schema::create('router_configuration_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_id')->constrained('mikrotik_routers')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('backup_data');
            $table->string('backup_type', 50)->default('manual');
            $table->text('notes')->nullable();
            $table->timestamp('created_at');

            $table->index('router_id');
            $table->index('backup_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_configuration_backups');
        Schema::dropIfExists('router_provisioning_logs');
        Schema::dropIfExists('router_configuration_templates');
    }
};
