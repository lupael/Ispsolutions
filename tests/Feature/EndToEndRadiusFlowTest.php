<?php

namespace Tests\Feature;

use App\Models\MikrotikRouter;
use App\Models\User;
use App\Services\PppSecretProvisioningService;
use App\Services\RadiusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Mocks\RouterosAPIMock;
use Tests\TestCase;

class EndToEndRadiusFlowTest extends TestCase
{
    use RefreshDatabase;

    private $radiusService;
    private $provisioningService;
    private $routerosApiMock;

    private $hotspotService;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->radiusService = $this->app->make(RadiusService::class);
        $this->provisioningService = $this->app->make(PppSecretProvisioningService::class);
        $this->hotspotService = $this->app->make(HotspotService::class);
        $this->routerosApiMock = new RouterosAPIMock();
        $this->provisioningService->setApi($this->routerosApiMock);
    }

    /** @test */
    public function pppoe_customer_can_be_provisioned_and_authenticated()
    {
        // 1. Create a customer and a router
        $customer = User::factory()->create([
            'is_subscriber' => true,
            'username' => 'testuser',
            'radius_password' => 'testpassword',
            'status' => 'active',
            'is_active' => true,
        ]);

        $router = MikrotikRouter::factory()->create();

        // 2. Provision the customer to the (mock) router
        $provisioned = $this->provisioningService->provisionPppSecret($customer, $router);
        $this->assertTrue($provisioned);

        // 3. Sync the customer to the RADIUS database
        $customer->syncToRadius();

        // 4. Attempt to authenticate the customer via RADIUS
        $isAuthenticated = $this->radiusService->authenticate($customer->username, $customer->radius_password);

        // 5. Assert that the authentication was successful
        $this->assertTrue($isAuthenticated);
    }

    /** @test */
    public function hotspot_customer_can_be_provisioned_and_authenticated()
    {
        // 1. Create a hotspot user
        $hotspotUser = \App\Models\HotspotUser::factory()->create();

        // 2. Sync the user to RADIUS
        $this->hotspotService->syncToRadius($hotspotUser);

        // 3. Attempt to authenticate the user via RADIUS using their MAC address
        $isAuthenticated = $this->radiusService->authenticate($hotspotUser->mac_address, $hotspotUser->mac_address);

        // 4. Assert that the authentication was successful
        $this->assertTrue($isAuthenticated);
    }
}
