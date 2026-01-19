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
        Schema::create('general_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('reference_number');
            $table->string('description');
            $table->enum('type', ['invoice', 'payment', 'expense', 'adjustment']);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('debit_account_id')->constrained('accounts')->onDelete('restrict');
            $table->foreignId('credit_account_id')->constrained('accounts')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['tenant_id', 'reference_number']);
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'type']);
            $table->index(['source_type', 'source_id']);
            $table->index('debit_account_id');
            $table->index('credit_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_entries');
    }
};
