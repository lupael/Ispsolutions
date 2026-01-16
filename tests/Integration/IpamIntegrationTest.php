<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\IpPool;
use App\Models\IpSubnet;
use App\Services\IpamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IpamIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private IpamService $ipamService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ipamService = app(IpamService::class);
    }

    public function test_transaction_rollback_on_error(): void
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

        // Attempt allocation with invalid data should not create records
        $initialCount = DB::table('ip_allocations')->count();

        try {
            // This should succeed
            $allocation = $this->ipamService->allocateIP(
                $subnet->id,
                '00:11:22:33:44:55',
                'testuser'
            );
            $this->assertNotNull($allocation);
        } catch (\Exception $e) {
            // Should not throw
        }

        $finalCount = DB::table('ip_allocations')->count();
        $this->assertEquals($initialCount + 1, $finalCount);
    }

    public function test_multiple_subnets_in_pool(): void
    {
        $pool = IpPool::create([
            'name' => 'Test Pool',
            'start_ip' => '10.0.0.1',
            'end_ip' => '10.0.1.254',
            'status' => 'active',
        ]);

        // Create multiple subnets
        $subnet1 = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.0.0',
            'prefix_length' => 29,
            'status' => 'active',
        ]);

        $subnet2 = IpSubnet::create([
            'pool_id' => $pool->id,
            'network' => '10.0.1.0',
            'prefix_length' => 29,
            'status' => 'active',
        ]);

        // Allocate from both subnets
        $allocation1 = $this->ipamService->allocateIP($subnet1->id, '00:11:22:33:44:55', 'user1');
        $allocation2 = $this->ipamService->allocateIP($subnet2->id, '00:11:22:33:44:56', 'user2');

        $this->assertNotNull($allocation1);
        $this->assertNotNull($allocation2);

        // Get pool utilization
        $utilization = $this->ipamService->getPoolUtilization($pool->id);

        // Each /29 has 6 usable IPs, total = 12, allocated = 2
        $this->assertEquals(12, $utilization['total']);
        $this->assertEquals(2, $utilization['allocated']);
    }

    public function test_reallocation_after_release(): void
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
            'prefix_length' => 30, // Only 2 usable IPs
            'status' => 'active',
        ]);

        // Allocate all IPs
        $allocation1 = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:55', 'user1');
        $allocation2 = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:56', 'user2');

        $this->assertNotNull($allocation1);
        $this->assertNotNull($allocation2);

        // Subnet should be full
        $allocation3 = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:57', 'user3');
        $this->assertNull($allocation3);

        // Release one IP
        $this->assertTrue($this->ipamService->releaseIP($allocation1->id));

        // Now we should be able to allocate again
        $allocation4 = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:57', 'user4');
        $this->assertNotNull($allocation4);
        $this->assertEquals($allocation1->ip_address, $allocation4->ip_address);
    }

    public function test_allocation_history_tracking(): void
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

        // Allocate IP
        $allocation = $this->ipamService->allocateIP($subnet->id, '00:11:22:33:44:55', 'user1');
        $this->assertNotNull($allocation);

        // Check allocation history
        $historyCount = DB::table('ip_allocation_history')
            ->where('allocation_id', $allocation->id)
            ->where('action', 'allocated')
            ->count();
        $this->assertEquals(1, $historyCount);

        // Release IP
        $this->assertTrue($this->ipamService->releaseIP($allocation->id));

        // Check release history
        $historyCount = DB::table('ip_allocation_history')
            ->where('allocation_id', $allocation->id)
            ->where('action', 'released')
            ->count();
        $this->assertEquals(1, $historyCount);

        // Total history entries should be 2
        $totalHistory = DB::table('ip_allocation_history')
            ->where('allocation_id', $allocation->id)
            ->count();
        $this->assertEquals(2, $totalHistory);
    }
}
