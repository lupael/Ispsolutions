<?php

namespace Tests\Unit\Services;

use App\Models\HotspotUser;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\HotspotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotspotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HotspotService $hotspotService;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hotspotService = new HotspotService;

        // Create a test tenant
        $this->tenant = Tenant::factory()->create();
    }

    public function test_can_generate_username()
    {
        $username = $this->hotspotService->generateUsername('01712345678');

        $this->assertStringStartsWith('hs_', $username);
        $this->assertStringContainsString('01712345678', $username);
    }

    public function test_can_generate_otp()
    {
        $otp = $this->hotspotService->generateOtp();

        $this->assertIsString($otp);
        $this->assertEquals(6, strlen($otp));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);
    }

    public function test_can_create_hotspot_user()
    {
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Hotspot Package',
            'price' => 100,
        ]);

        $data = [
            'mobile' => '01712345678',
            'password' => 'password123',
            'package_id' => $package->id,
            'tenant_id' => $this->tenant->id,
        ];

        $hotspotUser = $this->hotspotService->createHotspotUser($data);

        $this->assertInstanceOf(HotspotUser::class, $hotspotUser);
        $this->assertEquals('01712345678', $hotspotUser->mobile);
        $this->assertEquals($package->id, $hotspotUser->package_id);
        $this->assertEquals('active', $hotspotUser->status);
    }

    public function test_can_suspend_hotspot_user()
    {
        $hotspotUser = HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        $result = $this->hotspotService->suspendHotspotUser($hotspotUser->id);

        $this->assertTrue($result);
        $hotspotUser->refresh();
        $this->assertEquals('suspended', $hotspotUser->status);
    }

    public function test_can_reactivate_hotspot_user()
    {
        $hotspotUser = HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'suspended',
        ]);

        $result = $this->hotspotService->reactivateHotspotUser($hotspotUser->id);

        $this->assertTrue($result);
        $hotspotUser->refresh();
        $this->assertEquals('active', $hotspotUser->status);
    }

    public function test_can_renew_hotspot_subscription()
    {
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'validity_days' => 30,
        ]);

        $hotspotUser = HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'expired_at' => now()->subDays(5),
        ]);

        $result = $this->hotspotService->renewSubscription($hotspotUser->id, $package->id);

        $this->assertTrue($result);
        $hotspotUser->refresh();
        $this->assertTrue($hotspotUser->expired_at->greaterThan(now()));
    }

    public function test_can_get_expired_users()
    {
        // Create expired user
        HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'expired_at' => now()->subDays(1),
        ]);

        // Create active user
        HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'expired_at' => now()->addDays(30),
        ]);

        $expiredUsers = $this->hotspotService->getExpiredUsers();

        $this->assertGreaterThanOrEqual(1, $expiredUsers->count());
    }
}
