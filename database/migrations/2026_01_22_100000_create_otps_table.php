<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('mobile_number');
            $table->string('otp'); // Encrypted
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // Indexes for performance and cleanup
            $table->index(['mobile_number', 'verified_at', 'expires_at']);
            $table->index('expires_at'); // For cleanup queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
