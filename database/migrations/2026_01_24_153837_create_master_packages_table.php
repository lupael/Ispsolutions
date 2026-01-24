<?php

declare(strict_types=1);

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
        Schema::create('master_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('speed_upload')->nullable()->comment('Upload speed in kbps');
            $table->integer('speed_download')->nullable()->comment('Download speed in kbps');
            $table->bigInteger('volume_limit')->nullable()->comment('Volume limit in MB');
            $table->integer('validity_days')->default(30);
            $table->decimal('base_price', 10, 2);
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->boolean('is_trial_package')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('created_by');
            $table->index('status');
            $table->index('visibility');
            $table->index('is_trial_package');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_packages');
    }
};
