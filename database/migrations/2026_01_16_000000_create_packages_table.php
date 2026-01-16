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
            $table->decimal('price', 10, 2);
            $table->integer('bandwidth_upload')->nullable(); // in kbps
            $table->integer('bandwidth_download')->nullable(); // in kbps
            $table->integer('validity_days')->nullable();
            $table->enum('billing_type', ['daily', 'monthly', 'onetime'])->default('monthly');
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
