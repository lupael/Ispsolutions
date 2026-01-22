<?php

namespace Tests\Integration;

use App\Models\HotspotUser;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\HotspotService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotspotFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected HotspotService $hotspotService;

    protected SmsService $smsService;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotspotService = new HotspotService;
        $this->smsService = new SmsService;

        // Create a test tenant
        $this->tenant = Tenant::factory()->create();

        // Fake HTTP and enable SMS
        Http::fake();
        Config::set('sms.enabled', true);
    }

    public function test_complete_hotspot_self_signup_flow()
    {
        // Step 1: Create package
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Hotspot Package',
            'price' => 100,
            'validity_days' => 30,
        ]);

        // Step 2: Request OTP
        $mobile = '01712345678';
        $otp = $this->hotspotService->generateOtp();

        // Step 3: Send OTP via SMS
        $smsResult = $this->smsService->sendOtp($mobile, $otp);

        $this->assertIsArray($smsResult);

        // Step 4: Verify OTP and create hotspot user
        $userData = [
            'mobile' => $mobile,
            'password' => 'password123',
            'package_id' => $package->id,
            'tenant_id' => $this->tenant->id,
        ];

        $hotspotUser = $this->hotspotService->createHotspotUser($userData);

        $this->assertInstanceOf(HotspotUser::class, $hotspotUser);
        $this->assertEquals($mobile, $hotspotUser->mobile);
        $this->assertEquals('active', $hotspotUser->status);
        $this->assertNotNull($hotspotUser->expired_at);
    }

    public function test_hotspot_user_renewal_flow()
    {
        // Step 1: Create expired hotspot user
        $package = Package::factory()->create([
            'tenant_id' => $this->tenant->id,
            'validity_days' => 30,
        ]);

        $hotspotUser = HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'expired_at' => now()->subDays(5),
        ]);

        // Step 2: Renew subscription
        $result = $this->hotspotService->renewSubscription($hotspotUser->id, $package->id);

        $this->assertTrue($result);

        // Step 3: Verify expiration date is updated
        $hotspotUser->refresh();
        $this->assertTrue($hotspotUser->expired_at->greaterThan(now()));
    }

    public function test_hotspot_suspension_and_reactivation_flow()
    {
        // Step 1: Create active hotspot user
        $hotspotUser = HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        // Step 2: Suspend user
        $suspendResult = $this->hotspotService->suspendHotspotUser($hotspotUser->id);

        $this->assertTrue($suspendResult);
        $hotspotUser->refresh();
        $this->assertEquals('suspended', $hotspotUser->status);

        // Step 3: Reactivate user
        $reactivateResult = $this->hotspotService->reactivateHotspotUser($hotspotUser->id);

        $this->assertTrue($reactivateResult);
        $hotspotUser->refresh();
        $this->assertEquals('active', $hotspotUser->status);
    }

    public function test_automatic_expiration_handling()
    {
        // Step 1: Create expired hotspot user
        $hotspotUser = HotspotUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
            'expired_at' => now()->subDays(1),
        ]);

        // Step 2: Get expired users
        $expiredUsers = $this->hotspotService->getExpiredUsers();

        $this->assertGreaterThanOrEqual(1, $expiredUsers->count());

        // Step 3: Deactivate expired user
        $deactivateResult = $this->hotspotService->deactivateExpiredUsers();

        $this->assertIsArray($deactivateResult);
        $this->assertArrayHasKey('deactivated', $deactivateResult);
        $this->assertGreaterThanOrEqual(1, $deactivateResult['deactivated']);

        // Step 4: Verify user is deactivated
        $hotspotUser->refresh();
        $this->assertEquals('expired', $hotspotUser->status);
    }
}
