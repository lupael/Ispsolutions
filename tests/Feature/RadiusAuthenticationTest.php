<?php

namespace Tests\Feature;

use App\Contracts\RadiusServiceInterface;
use App\Models\RadReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiusAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private RadiusServiceInterface $radiusService;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'radius', '--path' => 'database/migrations/radius']);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->radiusService = $this->app->make(RadiusServiceInterface::class);
    }

    /** @test */
    public function a_customer_can_be_authenticated_via_radius()
    {
        // 1. Create a customer
        $customer = User::factory()->create([
            'is_subscriber' => true,
            'username' => 'testuser',
            'password' => 'testpassword',
            'status' => 'active',
        ]);

        // 2. Sync the customer to the RADIUS database
        $this->radiusService->createUser($customer->username, 'testpassword', ['Framed-IP-Address' => '192.168.1.100']);

        // 3. Attempt to authenticate the customer via RADIUS
        $response = $this->radiusService->authenticate(['username' => $customer->username, 'password' => 'testpassword']);

        // 4. Assert that the authentication was successful
        $this->assertTrue($response['success']);
    }

    /** @test */
    public function it_returns_radreply_attributes_on_successful_authentication()
    {
        // 1. Create a customer
        $customer = User::factory()->create([
            'is_subscriber' => true,
            'username' => 'testuser',
            'password' => 'testpassword',
            'status' => 'active',
        ]);

        // 2. Sync the customer to the RADIUS database with a reply attribute
        $this->radiusService->createUser($customer->username, 'testpassword', ['Framed-IP-Address' => '192.168.1.100']);
        RadReply::create([
            'username' => $customer->username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => '=',
            'value' => '1M/1M'
        ]);

        // 3. Attempt to authenticate the customer via RADIUS
        $response = $this->radiusService->authenticate(['username' => $customer->username, 'password' => 'testpassword']);

        // 4. Assert that the authentication was successful and reply attributes are returned
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('reply_attributes', $response);
        $this->assertArrayHasKey('Mikrotik-Rate-Limit', $response['reply_attributes']);
        $this->assertEquals('1M/1M', $response['reply_attributes']['Mikrotik-Rate-Limit']);
    }
}
