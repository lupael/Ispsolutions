<?php

namespace Tests\Feature;

use App\Models\IpPool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeleteIpPoolsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_bulk_delete_ip_pools()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        
        $this->actingAs($user);

        // Create test IP pools
        $pool1 = IpPool::factory()->create(['name' => 'Test Pool 1']);
        $pool2 = IpPool::factory()->create(['name' => 'Test Pool 2']);
        $pool3 = IpPool::factory()->create(['name' => 'Test Pool 3']);

        // Perform bulk delete on pool1 and pool2
        $response = $this->post(route('panel.admin.network.ipv4-pools.bulk-delete'), [
            'ids' => [$pool1->id, $pool2->id],
        ]);

        // Assert redirect with success message
        $response->assertRedirect(route('panel.admin.network.ipv4-pools'));
        $response->assertSessionHas('success');

        // Assert pools are deleted
        $this->assertDatabaseMissing('ip_pools', ['id' => $pool1->id]);
        $this->assertDatabaseMissing('ip_pools', ['id' => $pool2->id]);
        
        // Assert pool3 still exists
        $this->assertDatabaseHas('ip_pools', ['id' => $pool3->id]);
    }

    /** @test */
    public function it_validates_bulk_delete_request()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        
        $this->actingAs($user);

        // Test with empty ids array
        $response = $this->post(route('panel.admin.network.ipv4-pools.bulk-delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors('ids');
    }

    /** @test */
    public function it_validates_ids_exist_in_database()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
        
        $this->actingAs($user);

        // Test with non-existent id
        $response = $this->post(route('panel.admin.network.ipv4-pools.bulk-delete'), [
            'ids' => [99999],
        ]);

        $response->assertSessionHasErrors('ids.0');
    }
}
