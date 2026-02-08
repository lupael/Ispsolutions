<?php

namespace Tests\Unit\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrateNetworkUsersCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure fresh database structure for testing
        if (! Schema::hasTable('network_users')) {
            Schema::create('network_users', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('username')->unique();
                $table->string('password');
                $table->string('email')->nullable();
                $table->string('name')->nullable();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->string('service_type')->nullable();
                $table->string('connection_type')->nullable();
                $table->string('billing_type')->nullable();
                $table->string('device_type')->nullable();
                $table->string('mac_address')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('status')->default('active');
                $table->dateTime('expiry_date')->nullable();
                $table->text('radius_password')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('users', 'legacy_network_user_id')) {
            Schema::table('users', function ($table) {
                $table->unsignedBigInteger('legacy_network_user_id')->nullable()->index();
            });
        }
    }

    protected function tearDown(): void
    {
        // Clean up test data
        DB::table('network_users')->truncate();
        parent::tearDown();
    }

    public function test_migrate_network_users_creates_missing_users()
    {
        // Create a network user without a linked user
        $networkUser = DB::table('network_users')->insertGetId([
            'username' => 'testuser',
            'password' => 'plaintext_password', // In tests, this is fine
            'email' => 'test@example.com',
            'name' => 'Test User',
            'tenant_id' => 1,
            'status' => 'active',
            'service_type' => 'pppoe',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify no linked user initially
        $networkUserRecord = DB::table('network_users')->find($networkUser);
        $this->assertNull($networkUserRecord->user_id);

        // Run the migration command
        $this->artisan('migrate:network-users')
            ->expectsOutput('Found 1 network_users rows with no linked user')
            ->expectsOutput('Completed. Processed 1 records.')
            ->assertExitCode(0);

        // Verify user was created and linked
        $networkUserRecord = DB::table('network_users')->find($networkUser);
        $this->assertNotNull($networkUserRecord->user_id);

        // Verify the user has correct attributes
        $user = DB::table('users')->find($networkUserRecord->user_id);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals(1, $user->tenant_id);
        $this->assertEquals($networkUser, $user->legacy_network_user_id);
    }

    public function test_migrate_network_users_skips_already_linked()
    {
        // Create a user first
        $userId = DB::table('users')->insertGetId([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'hashed_password',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a network user already linked
        DB::table('network_users')->insert([
            'user_id' => $userId,
            'username' => 'linkeduser',
            'password' => 'plaintext_password',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the migration command
        $this->artisan('migrate:network-users')
            ->expectsOutput('Found 0 network_users rows with no linked user')
            ->assertExitCode(0);
    }

    public function test_migrate_network_users_is_idempotent()
    {
        // Create a network user without a linked user
        DB::table('network_users')->insert([
            'username' => 'testuser',
            'password' => 'plaintext_password',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Run the migration command first time
        $this->artisan('migrate:network-users')->assertExitCode(0);

        $userCount = DB::table('users')->count();

        // Run the migration command second time
        $this->artisan('migrate:network-users')
            ->expectsOutput('Found 0 network_users rows with no linked user')
            ->assertExitCode(0);

        // Verify no duplicate users created
        $this->assertEquals($userCount, DB::table('users')->count());
    }

    public function test_migrate_network_users_handles_no_network_users_table()
    {
        // Drop the network_users table temporarily
        Schema::dropIfExists('network_users_backup');
        Schema::rename('network_users', 'network_users_backup');

        try {
            // Run the migration command
            $this->artisan('migrate:network-users')
                ->expectsOutput('network_users table does not exist')
                ->assertExitCode(0);
        } finally {
            // Restore the table
            Schema::rename('network_users_backup', 'network_users');
        }
    }
}
