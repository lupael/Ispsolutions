<?php

namespace Tests\Feature;

use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeletePppoeProfilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user with admin role for authentication
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function it_can_bulk_delete_pppoe_profiles()
    {
        $this->actingAs($this->user);

        // Create a router first
        $router = MikrotikRouter::factory()->create();

        // Create test PPPoE profiles
        $profile1 = MikrotikProfile::factory()->create([
            'name' => 'Profile 1',
            'router_id' => $router->id,
        ]);
        $profile2 = MikrotikProfile::factory()->create([
            'name' => 'Profile 2',
            'router_id' => $router->id,
        ]);
        $profile3 = MikrotikProfile::factory()->create([
            'name' => 'Profile 3',
            'router_id' => $router->id,
        ]);

        // Perform bulk delete on profile1 and profile2
        $response = $this->post(route('panel.admin.network.pppoe-profiles.bulk-delete'), [
            'ids' => [$profile1->id, $profile2->id],
        ]);

        // Assert redirect with success message
        $response->assertRedirect(route('panel.admin.network.pppoe-profiles'));
        $response->assertSessionHas('success');

        // Assert profiles are deleted
        $this->assertDatabaseMissing('mikrotik_profiles', ['id' => $profile1->id]);
        $this->assertDatabaseMissing('mikrotik_profiles', ['id' => $profile2->id]);
        
        // Assert profile3 still exists
        $this->assertDatabaseHas('mikrotik_profiles', ['id' => $profile3->id]);
    }

    /** @test */
    public function it_validates_bulk_delete_request()
    {
        $this->actingAs($this->user);

        // Test with empty ids array
        $response = $this->post(route('panel.admin.network.pppoe-profiles.bulk-delete'), [
            'ids' => [],
        ]);

        $response->assertSessionHasErrors('ids');
    }

    /** @test */
    public function it_validates_ids_exist_in_database()
    {
        $this->actingAs($this->user);

        // Test with non-existent id
        $response = $this->post(route('panel.admin.network.pppoe-profiles.bulk-delete'), [
            'ids' => [99999],
        ]);

        $response->assertSessionHasErrors('ids.0');
    }
}
