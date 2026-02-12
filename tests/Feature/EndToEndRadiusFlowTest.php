<?php

namespace Tests\Feature;

use App\Contracts\RadiusServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndRadiusFlowTest extends TestCase
{
    use RefreshDatabase;

    private RadiusServiceInterface $radiusService;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'radius', '--path' => 'database/migrations/radius']);
        $this->radiusService = $this->app->make(RadiusServiceInterface::class);
    }

    /** @test */
    public function it_simulates_a_pppoe_authentication_flow()
    {
        // 1. Create a user with a PPPoE service type
        $user = User::factory()->create([
            'username' => 'pppoeuser',
            'password' => 'testpassword',
            'service_type' => 'pppoe',
        ]);
        $this->radiusService->createUser($user->username, 'testpassword', [
            'Framed-IP-Address' => '192.168.1.100',
            'Calling-Station-Id' => '00:11:22:33:44:55',
        ]);

        // 2. Simulate a PPPoE authentication request
        $response = $this->radiusService->authenticate([
            'username' => 'pppoeuser',
            'password' => 'testpassword',
            'mac_address' => '00:11:22:33:44:55',
        ]);

        // 3. Assert that the authentication was successful and the correct attributes are returned
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('reply_attributes', $response);
        $this->assertEquals('192.168.1.100', $response['reply_attributes']['Framed-IP-Address']);
    }

    /** @test */
    public function it_simulates_a_hotspot_authentication_flow()
    {
        // 1. Create a user with a Hotspot service type
        $user = User::factory()->create([
            'username' => 'hotspotuser',
            'password' => 'testpassword',
            'service_type' => 'hotspot',
        ]);
        $this->radiusService->createUser($user->username, 'testpassword', [
            'Framed-IP-Address' => '192.168.2.100',
            'Calling-Station-Id' => 'AA:BB:CC:DD:EE:FF',
        ]);

        // 2. Simulate a Hotspot authentication request
        $response = $this->radiusService->authenticate([
            'username' => 'hotspotuser',
            'password' => 'testpassword',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
        ]);

        // 3. Assert that the authentication was successful and the correct attributes are returned
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('reply_attributes', $response);
        $this->assertEquals('192.168.2.100', $response['reply_attributes']['Framed-IP-Address']);
    }
}