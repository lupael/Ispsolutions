<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Services\RadiusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiusIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private RadiusService $radiusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->radiusService = app(RadiusService::class);

        // Run migrations for RADIUS database
        $this->artisan('migrate', ['--database' => 'radius']);
    }

    public function test_full_user_lifecycle(): void
    {
        // Create user
        $result = $this->radiusService->createUser('testuser', 'testpass', [
            'Service-Type' => 'Framed-User',
        ]);
        $this->assertTrue($result);

        // Verify user exists
        $check = RadCheck::where('username', 'testuser')->first();
        $this->assertNotNull($check);

        // Update user
        $result = $this->radiusService->updateUser('testuser', [
            'password' => 'newpass',
            'Framed-IP-Address' => '10.0.0.1',
        ]);
        $this->assertTrue($result);

        // Verify update
        $check = RadCheck::where('username', 'testuser')
            ->where('attribute', 'Cleartext-Password')
            ->first();
        $this->assertEquals('newpass', $check->value);

        // Delete user
        $result = $this->radiusService->deleteUser('testuser');
        $this->assertTrue($result);

        // Verify deletion
        $check = RadCheck::where('username', 'testuser')->first();
        $this->assertNull($check);
    }

    public function test_sync_multiple_users(): void
    {
        $package = Package::create([
            'name' => 'Test Package',
            'price' => 100.00,
            'status' => 'active',
        ]);

        // Create multiple network users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = NetworkUser::create([
                'username' => "user{$i}",
                'password' => "pass{$i}",
                'service_type' => 'pppoe',
                'package_id' => $package->id,
                'status' => 'active',
            ]);
        }

        // Sync all users
        foreach ($users as $user) {
            $result = $this->radiusService->syncUser($user);
            $this->assertTrue($result);
        }

        // Verify all users in RADIUS
        $radiusUsers = RadCheck::whereIn('username', array_map(fn ($u) => $u->username, $users))
            ->count();
        $this->assertEquals(5, $radiusUsers);

        // Deactivate one user
        $users[0]->update(['status' => 'inactive']);
        $result = $this->radiusService->syncUser($users[0]);
        $this->assertTrue($result);

        // Verify user removed from RADIUS
        $radiusUsers = RadCheck::whereIn('username', array_map(fn ($u) => $u->username, $users))
            ->count();
        $this->assertEquals(4, $radiusUsers);
    }

    public function test_concurrent_operations(): void
    {
        // Create user
        $this->radiusService->createUser('testuser', 'testpass');

        // Concurrent updates (simulated)
        $result1 = $this->radiusService->updateUser('testuser', [
            'Framed-IP-Address' => '10.0.0.1',
        ]);

        $result2 = $this->radiusService->updateUser('testuser', [
            'Service-Type' => 'Framed-User',
        ]);

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        // Verify both attributes exist
        $attributes = RadReply::where('username', 'testuser')->count();
        $this->assertEquals(2, $attributes);
    }

    public function test_database_connection_separation(): void
    {
        // Verify RADIUS data goes to separate database
        $package = Package::create([
            'name' => 'Test Package',
            'price' => 100.00,
            'status' => 'active',
        ]);

        $user = NetworkUser::create([
            'username' => 'testuser',
            'password' => 'testpass',
            'service_type' => 'pppoe',
            'package_id' => $package->id,
            'status' => 'active',
        ]);

        // Sync user
        $this->radiusService->syncUser($user);

        // NetworkUser should be in default database
        $this->assertDatabaseHas('network_users', [
            'username' => 'testuser',
        ]);

        // RadCheck should be in radius database
        $this->assertDatabaseHas('radcheck', [
            'username' => 'testuser',
        ], 'radius');
    }
}
