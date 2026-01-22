<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IpPool;
use App\Models\IpSubnet;
use Database\Seeders\IpPoolSeeder;
use Database\Seeders\IpSubnetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpSubnetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ip_subnet_seeder_creates_subnets_successfully(): void
    {
        // First seed the IP pools
        $this->seed(IpPoolSeeder::class);

        // Verify pools were created
        $this->assertDatabaseCount('ip_pools', 2);

        $publicPool = IpPool::where('pool_type', 'public')->first();
        $privatePool = IpPool::where('pool_type', 'private')->first();

        $this->assertNotNull($publicPool);
        $this->assertNotNull($privatePool);

        // Now seed the IP subnets
        $this->seed(IpSubnetSeeder::class);

        // Verify subnets were created
        $this->assertDatabaseCount('ip_subnets', 3);

        // Verify public subnet
        $publicSubnet = IpSubnet::where('network', '203.0.113.0')->first();
        $this->assertNotNull($publicSubnet);
        $this->assertEquals($publicPool->id, $publicSubnet->pool_id);
        $this->assertEquals(24, $publicSubnet->prefix_length);
        $this->assertEquals('203.0.113.1', $publicSubnet->gateway);
        $this->assertEquals(100, $publicSubnet->vlan_id);
        $this->assertEquals('Public subnet for customer connections', $publicSubnet->description);
        $this->assertEquals('active', $publicSubnet->status);

        // Verify private subnets
        $privateSubnet1 = IpSubnet::where('network', '192.168.100.0')->first();
        $this->assertNotNull($privateSubnet1);
        $this->assertEquals($privatePool->id, $privateSubnet1->pool_id);
        $this->assertEquals('Private subnet for internal network', $privateSubnet1->description);

        $privateSubnet2 = IpSubnet::where('network', '192.168.101.0')->first();
        $this->assertNotNull($privateSubnet2);
        $this->assertEquals($privatePool->id, $privateSubnet2->pool_id);
        $this->assertEquals('Private subnet for management', $privateSubnet2->description);
    }

    public function test_ip_subnet_factory_creates_subnet_with_pool_id(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'pool_type' => 'public',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::factory()->create([
            'pool_id' => $pool->id,
        ]);

        $this->assertNotNull($subnet);
        $this->assertEquals($pool->id, $subnet->pool_id);
        $this->assertNotNull($subnet->network);
        $this->assertNotNull($subnet->prefix_length);
        $this->assertEquals('active', $subnet->status);
    }
}
