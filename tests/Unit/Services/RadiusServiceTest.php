<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Services\RadiusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiusServiceTest extends TestCase
{
    use RefreshDatabase;

    private RadiusService $radiusService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->radiusService = new RadiusService;

        // Run migrations for both databases
        $this->artisan('migrate', ['--database' => 'radius']);
    }

    public function test_create_user_successfully(): void
    {
        $result = $this->radiusService->createUser('testuser', 'testpass');

        $this->assertTrue($result);

        // Verify user in radcheck
        $this->assertDatabaseHas('radcheck', [
            'username' => 'testuser',
            'attribute' => 'Cleartext-Password',
            'value' => 'testpass',
        ], 'radius');
    }

    public function test_create_user_with_attributes(): void
    {
        $attributes = [
            'Framed-Protocol' => 'PPP',
            'Service-Type' => 'Framed-User',
        ];

        $result = $this->radiusService->createUser('testuser', 'testpass', $attributes);

        $this->assertTrue($result);

        // Verify attributes in radreply
        foreach ($attributes as $attribute => $value) {
            $this->assertDatabaseHas('radreply', [
                'username' => 'testuser',
                'attribute' => $attribute,
                'value' => $value,
            ], 'radius');
        }
    }

    public function test_update_user_password(): void
    {
        // Create user first
        $this->radiusService->createUser('testuser', 'oldpass');

        // Update password
        $result = $this->radiusService->updateUser('testuser', [
            'password' => 'newpass',
        ]);

        $this->assertTrue($result);

        // Verify password updated
        $check = RadCheck::where('username', 'testuser')
            ->where('attribute', 'Cleartext-Password')
            ->first();

        $this->assertEquals('newpass', $check->value);
    }

    public function test_update_user_attributes(): void
    {
        // Create user first
        $this->radiusService->createUser('testuser', 'testpass');

        // Update attributes
        $result = $this->radiusService->updateUser('testuser', [
            'Framed-IP-Address' => '10.0.0.1',
        ]);

        $this->assertTrue($result);

        // Verify attribute created
        $this->assertDatabaseHas('radreply', [
            'username' => 'testuser',
            'attribute' => 'Framed-IP-Address',
            'value' => '10.0.0.1',
        ], 'radius');
    }

    public function test_delete_user(): void
    {
        // Create user first
        $this->radiusService->createUser('testuser', 'testpass', [
            'Service-Type' => 'Framed-User',
        ]);

        // Delete user
        $result = $this->radiusService->deleteUser('testuser');

        $this->assertTrue($result);

        // Verify user removed from radcheck
        $this->assertDatabaseMissing('radcheck', [
            'username' => 'testuser',
        ], 'radius');

        // Verify attributes removed from radreply
        $this->assertDatabaseMissing('radreply', [
            'username' => 'testuser',
        ], 'radius');
    }

    public function test_sync_active_user_creates_radius_entry(): void
    {
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

        $result = $this->radiusService->syncUser($user);

        $this->assertTrue($result);

        // Verify user in RADIUS
        $this->assertDatabaseHas('radcheck', [
            'username' => 'testuser',
        ], 'radius');
    }

    public function test_sync_inactive_user_removes_radius_entry(): void
    {
        // Create user in RADIUS first
        $this->radiusService->createUser('testuser', 'testpass');

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
            'status' => 'inactive',
        ]);

        $result = $this->radiusService->syncUser($user);

        $this->assertTrue($result);

        // Verify user removed from RADIUS
        $this->assertDatabaseMissing('radcheck', [
            'username' => 'testuser',
        ], 'radius');
    }

    public function test_get_accounting_data_with_no_sessions(): void
    {
        $data = $this->radiusService->getAccountingData('testuser');

        $this->assertEquals('testuser', $data['username']);
        $this->assertEquals(0, $data['total_sessions']);
        $this->assertEquals(0, $data['active_sessions']);
        $this->assertEquals(0, $data['total_upload_bytes']);
        $this->assertEquals(0, $data['total_download_bytes']);
    }

    public function test_get_accounting_data_with_sessions(): void
    {
        // Create test accounting sessions
        RadAcct::create([
            'acctsessionid' => 'session1',
            'acctuniqueid' => 'unique1',
            'username' => 'testuser',
            'nasipaddress' => '192.168.1.1',
            'acctstarttime' => now()->subHours(2),
            'acctstoptime' => now()->subHours(1),
            'acctsessiontime' => 3600,
            'acctinputoctets' => 1000000,
            'acctoutputoctets' => 2000000,
            'framedipaddress' => '10.0.0.1',
        ]);

        RadAcct::create([
            'acctsessionid' => 'session2',
            'acctuniqueid' => 'unique2',
            'username' => 'testuser',
            'nasipaddress' => '192.168.1.1',
            'acctstarttime' => now()->subMinutes(30),
            'acctstoptime' => null, // Active session
            'acctsessiontime' => 1800,
            'acctinputoctets' => 500000,
            'acctoutputoctets' => 1000000,
            'framedipaddress' => '10.0.0.2',
        ]);

        $data = $this->radiusService->getAccountingData('testuser');

        $this->assertEquals('testuser', $data['username']);
        $this->assertEquals(2, $data['total_sessions']);
        $this->assertEquals(1, $data['active_sessions']);
        $this->assertEquals(1500000, $data['total_upload_bytes']);
        $this->assertEquals(3000000, $data['total_download_bytes']);
        $this->assertEquals(5400, $data['total_session_time']);
    }
}
