<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('bandwidth_up')->nullable(); // in kbps
            $table->integer('bandwidth_down')->nullable(); // in kbps
            $table->decimal('price', 10, 2);
            $table->string('billing_cycle')->default('monthly');
            $table->integer('validity_days')->nullable();
            $table->enum('billing_type', ['daily', 'monthly', 'onetime'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
