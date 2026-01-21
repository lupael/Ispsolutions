<?php

declare(strict_types=1);

namespace Tests\Unit\Seeders;

use App\Models\IpPool;
use Database\Seeders\IpPoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpPoolSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ip_pool_seeder_creates_pools_successfully(): void
    {
        // Run the seeder
        $seeder = new IpPoolSeeder();
        $seeder->run();

        // Assert that the pools were created
        $this->assertDatabaseCount('ip_pools', 2);

        // Assert public pool exists with correct attributes
        $publicPool = IpPool::where('name', 'Public Pool 1')->first();
        $this->assertNotNull($publicPool);
        $this->assertEquals('Main public IP address pool', $publicPool->description);
        $this->assertEquals('public', $publicPool->pool_type);
        $this->assertEquals('active', $publicPool->status);

        // Assert private pool exists with correct attributes
        $privatePool = IpPool::where('name', 'Private Pool 1')->first();
        $this->assertNotNull($privatePool);
        $this->assertEquals('Private network addresses for internal use', $privatePool->description);
        $this->assertEquals('private', $privatePool->pool_type);
        $this->assertEquals('active', $privatePool->status);
    }

    public function test_ip_pool_seeder_does_not_duplicate_pools(): void
    {
        // Run the seeder twice
        $seeder = new IpPoolSeeder();
        $seeder->run();
        $seeder->run();

        // Assert that only 2 pools exist (no duplicates)
        $this->assertDatabaseCount('ip_pools', 2);
    }
}
