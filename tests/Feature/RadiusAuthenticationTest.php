<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RadiusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiusAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private $radiusService;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->radiusService = $this->app->make(RadiusService::class);
    }

    /** @test */
    public function a_customer_can_be_authenticated_via_radius()
    {
        // 1. Create a customer
        $customer = User::factory()->create([
            'is_subscriber' => true,
            'username' => 'testuser',
            'radius_password' => 'testpassword',
            'status' => 'active',
            'is_active' => true,
        ]);

        // 2. Sync the customer to the RADIUS database
        $customer->syncToRadius();

        // 3. Attempt to authenticate the customer via RADIUS
        $isAuthenticated = $this->radiusService->authenticate($customer->username, $customer->radius_password);

        // 4. Assert that the authentication was successful
        $this->assertTrue($isAuthenticated);
    }
}
