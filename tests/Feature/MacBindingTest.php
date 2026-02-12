<?php

namespace Tests\Feature;

use App\Contracts\RadiusServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacBindingTest extends TestCase
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
    public function it_authenticates_a_user_with_a_matching_mac_address()
    {
        // 1. Create a user with a specific MAC address
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => 'testpassword',
        ]);
        $this->radiusService->createUser($user->username, 'testpassword', ['mac_address' => '00:11:22:33:44:55']);

        // 2. Attempt to authenticate with a matching MAC address
        $response = $this->radiusService->authenticate([
            'username' => 'testuser',
            'password' => 'testpassword',
            'mac_address' => '00:11:22:33:44:55',
        ]);

        // 3. Assert that the authentication was successful
        $this->assertTrue($response['success']);
    }

    /** @test */
    public function it_fails_to_authenticate_a_user_with_a_mismatched_mac_address()
    {
        // 1. Create a user with a specific MAC address
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => 'testpassword',
        ]);
        $this->radiusService->createUser($user->username, 'testpassword', ['mac_address' => '00:11:22:33:44:55']);

        // 2. Attempt to authenticate with a different MAC address
        $response = $this->radiusService->authenticate([
            'username' => 'testuser',
            'password' => 'testpassword',
            'mac_address' => '66:77:88:99:AA:BB',
        ]);

        // 3. Assert that the authentication failed
        $this->assertFalse($response['success']);
        $this->assertEquals('Authentication failed: MAC address mismatch', $response['message']);
    }

    /** @test */
    public function it_fails_to_authenticate_a_user_without_a_mac_address_if_one_is_required()
    {
        // 1. Create a user with a specific MAC address
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => 'testpassword',
        ]);
        $this->radiusService->createUser($user->username, 'testpassword', ['mac_address' => '00:11:22:33:44:55']);

        // 2. Attempt to authenticate without a MAC address
        $response = $this->radiusService->authenticate([
            'username' => 'testuser',
            'password' => 'testpassword',
        ]);

        // 3. Assert that the authentication failed
        $this->assertFalse($response['success']);
        $this->assertEquals('Authentication failed: MAC address mismatch', $response['message']);
    }
}
