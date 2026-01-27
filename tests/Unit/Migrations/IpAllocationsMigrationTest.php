<?php

declare(strict_types=1);

namespace Tests\Unit\Migrations;

use App\Models\IpAllocation;
use App\Models\IpPool;
use App\Models\IpSubnet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IpAllocationsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ip_allocations_table_has_user_id_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('ip_allocations', 'user_id'),
            'ip_allocations table should have user_id column'
        );
    }

    public function test_ip_allocations_table_has_allocation_type_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('ip_allocations', 'allocation_type'),
            'ip_allocations table should have allocation_type column'
        );
    }

    public function test_can_create_ip_allocation_with_user_id_and_allocation_type(): void
    {
        // Create a pool and subnet
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'gateway' => '10.0.0.1',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 24,
            'gateway' => '10.0.0.1',
            'status' => 'active',
        ]);

        $user = User::factory()->create();

        // Create an IP allocation with user_id and allocation_type
        $allocation = IpAllocation::create([
            'subnet_id' => $subnet->id,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.100',
            'mac_address' => '00:11:22:33:44:55',
            'username' => 'testuser',
            'status' => 'allocated',
            'allocation_type' => 'static',
            'allocated_at' => now(),
        ]);

        $this->assertDatabaseHas('ip_allocations', [
            'id' => $allocation->id,
            'user_id' => $user->id,
            'allocation_type' => 'static',
        ]);
    }

    public function test_can_query_ip_allocations_by_user_id(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'gateway' => '10.0.0.1',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 24,
            'gateway' => '10.0.0.1',
            'status' => 'active',
        ]);

        $user = User::factory()->create();

        IpAllocation::create([
            'subnet_id' => $subnet->id,
            'user_id' => $user->id,
            'ip_address' => '10.0.0.100',
            'status' => 'allocated',
            'allocation_type' => 'static',
            'allocated_at' => now(),
        ]);

        // Test the query that was failing in ServiceController
        $staticIps = IpAllocation::where('user_id', $user->id)
            ->where('allocation_type', 'static')
            ->get();

        $this->assertCount(1, $staticIps);
        $this->assertEquals($user->id, $staticIps->first()->user_id);
        $this->assertEquals('static', $staticIps->first()->allocation_type);
    }

    public function test_can_query_unallocated_ips(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'gateway' => '10.0.0.1',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 24,
            'gateway' => '10.0.0.1',
            'status' => 'active',
        ]);

        // Create allocated IP
        IpAllocation::create([
            'subnet_id' => $subnet->id,
            'user_id' => User::factory()->create()->id,
            'ip_address' => '10.0.0.100',
            'status' => 'allocated',
            'allocation_type' => 'static',
        ]);

        // Create unallocated IP
        IpAllocation::create([
            'subnet_id' => $subnet->id,
            'user_id' => null,
            'ip_address' => '10.0.0.101',
            'status' => 'reserved',
            'allocation_type' => 'dynamic',
        ]);

        // Test the query from ServiceController
        $availableCount = IpAllocation::whereNull('user_id')->count();

        $this->assertEquals(1, $availableCount);
    }
}
