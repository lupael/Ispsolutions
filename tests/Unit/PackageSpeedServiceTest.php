<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\MikrotikRouter;
use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\PackageProfileMapping;
use App\Services\MikrotikService;
use App\Services\PackageSpeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PackageSpeedServiceTest extends TestCase
{
    use RefreshDatabase;

    private PackageSpeedService $packageSpeedService;

    private Package $package;

    private MikrotikRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        $mikrotikService = new MikrotikService;
        $this->packageSpeedService = new PackageSpeedService($mikrotikService);

        $this->router = MikrotikRouter::create([
            'name' => 'Test Router',
            'ip_address' => 'localhost',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ]);

        $this->package = Package::create([
            'name' => 'Test Package',
            'description' => 'Test package description',
            'price' => 100.00,
            'bandwidth_up' => 10,
            'bandwidth_down' => 10,
            'validity_days' => 30,
            'billing_type' => 'monthly',
            'status' => 'active',
        ]);
    }

    public function test_map_package_to_profile_successfully(): void
    {
        $result = $this->packageSpeedService->mapPackageToProfile(
            $this->package->id,
            $this->router->id,
            'profile-10mbps'
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('package_profile_mappings', [
            'package_id' => $this->package->id,
            'router_id' => $this->router->id,
            'profile_name' => 'profile-10mbps',
        ]);
    }

    public function test_get_profile_for_package(): void
    {
        PackageProfileMapping::create([
            'package_id' => $this->package->id,
            'router_id' => $this->router->id,
            'profile_name' => 'profile-10mbps',
            'speed_control_method' => 'router',
        ]);

        $profile = $this->packageSpeedService->getProfileForPackage(
            $this->package->id,
            $this->router->id
        );

        $this->assertEquals('profile-10mbps', $profile);
    }

    public function test_get_profile_for_package_not_found(): void
    {
        $profile = $this->packageSpeedService->getProfileForPackage(
            $this->package->id,
            $this->router->id
        );

        $this->assertNull($profile);
    }

    public function test_map_package_to_nonexistent_package_fails(): void
    {
        $result = $this->packageSpeedService->mapPackageToProfile(
            99999,
            $this->router->id,
            'profile-10mbps'
        );

        $this->assertFalse($result);
    }

    public function test_apply_speed_to_user_without_router_fails(): void
    {
        $user = NetworkUser::create([
            'username' => 'testuser',
            'password' => 'password123',
            'package_id' => $this->package->id,
            'router_id' => null,
            'status' => 'active',
        ]);

        $result = $this->packageSpeedService->applySpeedToUser($user->id);

        $this->assertFalse($result);
    }

    public function test_apply_speed_to_user_without_mapping_fails(): void
    {
        $user = NetworkUser::create([
            'username' => 'testuser',
            'password' => 'password123',
            'package_id' => $this->package->id,
            'router_id' => $this->router->id,
            'status' => 'active',
        ]);

        $result = $this->packageSpeedService->applySpeedToUser($user->id);

        $this->assertFalse($result);
    }
}
