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
        Schema::create('operator_sms_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->decimal('rate_per_sms', 10, 4);
            $table->integer('bulk_rate_threshold')->default(100);
            $table->decimal('bulk_rate_per_sms', 10, 4)->nullable();
            $table->timestamps();

            $table->index('operator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operator_sms_rates');
    }
};
