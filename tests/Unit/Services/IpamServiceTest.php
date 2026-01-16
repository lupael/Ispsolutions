<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\IpAllocation;
use App\Models\IpAllocationHistory;
use App\Models\IpPool;
use App\Models\IpSubnet;
use App\Services\IpamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IpamServiceTest extends TestCase
{
    use RefreshDatabase;

    private IpamService $ipamService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ipamService = new IpamService();
    }

    public function test_allocate_ip_successfully(): void
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

        // Allocate an IP
        $allocation = $this->ipamService->allocateIP(
            $subnet->id,
            '00:11:22:33:44:55',
            'testuser'
        );

        $this->assertNotNull($allocation);
        $this->assertEquals($subnet->id, $allocation->subnet_id);
        $this->assertEquals('00:11:22:33:44:55', $allocation->mac_address);
        $this->assertEquals('testuser', $allocation->username);
        $this->assertEquals('allocated', $allocation->status);
        $this->assertNotNull($allocation->allocated_at);

        // Verify history was created
        $this->assertDatabaseHas('ip_allocation_history', [
            'allocation_id' => $allocation->id,
            'ip_address' => $allocation->ip_address,
            'action' => 'allocated',
        ]);
    }

    public function test_allocate_ip_from_inactive_subnet_fails(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 24,
            'status' => 'inactive',
        ]);

        $allocation = $this->ipamService->allocateIP(
            $subnet->id,
            '00:11:22:33:44:55',
            'testuser'
        );

        $this->assertNull($allocation);
    }

    public function test_allocate_ip_when_subnet_full(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'status' => 'active',
        ]);

        // Create a /30 subnet (only 2 usable IPs)
        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 30,
            'status' => 'active',
        ]);

        // Allocate all IPs
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:55', 'user1');
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:56', 'user2');

        // Try to allocate when full
        $allocation = $this->ipamService->allocateIP(
            $subnet->id,
            '00:11:22:33:44:57',
            'user3'
        );

        $this->assertNull($allocation);
    }

    public function test_release_ip_successfully(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 24,
            'status' => 'active',
        ]);

        // Allocate an IP
        $allocation = $this->ipamService->allocateIP(
            $subnet->id,
            '00:11:22:33:44:55',
            'testuser'
        );

        // Release the IP
        $result = $this->ipamService->releaseIP($allocation->id);

        $this->assertTrue($result);
        
        // Verify allocation was updated
        $allocation->refresh();
        $this->assertEquals('released', $allocation->status);
        $this->assertNotNull($allocation->released_at);

        // Verify history was created
        $this->assertDatabaseHas('ip_allocation_history', [
            'allocation_id' => $allocation->id,
            'action' => 'released',
        ]);
    }

    public function test_release_nonexistent_allocation_fails(): void
    {
        $result = $this->ipamService->releaseIP(99999);
        $this->assertFalse($result);
    }

    public function test_get_available_ips(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'status' => 'active',
        ]);

        // Create a /29 subnet (6 usable IPs)
        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 29,
            'status' => 'active',
        ]);

        // Allocate 2 IPs
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:55', 'user1');
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:56', 'user2');

        // Get available IPs
        $availableIPs = $this->ipamService->getAvailableIPs($subnet->id);

        // Should have 4 available IPs (6 - 2 allocated)
        $this->assertCount(4, $availableIPs);
    }

    public function test_get_pool_utilization(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'status' => 'active',
        ]);

        // Create a /29 subnet (6 usable IPs)
        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 29,
            'status' => 'active',
        ]);

        // Allocate 3 IPs
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:55', 'user1');
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:56', 'user2');
        $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:57', 'user3');

        // Get utilization
        $utilization = $this->ipamService->getPoolUtilization($pool->id);

        $this->assertEquals(6, $utilization['total']);
        $this->assertEquals(3, $utilization['allocated']);
        $this->assertEquals(3, $utilization['available']);
        $this->assertEquals(50.0, $utilization['utilization_percent']);
    }

    public function test_sequential_allocations_from_same_subnet(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.0.254',
            'status' => 'active',
        ]);

        $subnet = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 29, // 6 usable IPs
            'status' => 'active',
        ]);

        // Simulate sequential allocations (actual concurrent testing would require ParaTest)
        $allocation1 = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:55', 'user1');
        $allocation2 = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:56', 'user2');

        $this->assertNotNull($allocation1);
        $this->assertNotNull($allocation2);
        $this->assertNotEquals($allocation1->ip_address, $allocation2->ip_address);
    }
}
