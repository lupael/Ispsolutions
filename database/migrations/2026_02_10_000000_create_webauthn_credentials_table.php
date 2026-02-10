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
        Schema::create('webauthn_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->default('My Security Key'); // User-friendly name for the credential
            $table->string('credential_id')->unique(); // The actual credential ID from WebAuthn
            $table->text('public_key'); // Serialized public key
            $table->text('credential_public_key'); // Raw credential public key
            $table->bigInteger('counter')->default(0); // Signature counter to prevent cloning
            $table->string('transports')->nullable(); // JSON array of transport types (usb, nfc, ble, etc.)
            $table->string('aaguid')->nullable(); // Authenticator AAGUID if available
            $table->string('attestation_type')->nullable(); // Attestation format (direct, indirect, self, none)
            $table->boolean('is_primary')->default(false); // Whether this is the primary credential
            $table->boolean('is_active')->default(true); // Whether this credential is active
            $table->ipAddress('registration_ip')->nullable(); // IP address used during registration
            $table->string('user_agent')->nullable(); // User agent used during registration
            $table->timestamp('last_used_at')->nullable(); // Last successful authentication using this credential
            $table->ipAddress('last_used_ip')->nullable(); // Last IP used with this credential
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['user_id', 'is_active']);
            $table->index('credential_id');
        });

        Schema::create('webauthn_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('challenge')->unique(); // Base64 encoded challenge
            $table->enum('type', ['registration', 'authentication'])->default('authentication');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamp('expires_at'); // Challenge must be used within 5 minutes
            $table->timestamps();
            
            // Index for cleanup
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webauthn_challenges');
        Schema::dropIfExists('webauthn_credentials');
    }
};
