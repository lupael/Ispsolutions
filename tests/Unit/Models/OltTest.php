<?php

namespace Tests\Unit\Models;

use App\Models\Olt;
use App\Models\Onu;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltTest extends TestCase
{
    use RefreshDatabase;

    public function test_olt_can_be_created(): void
    {
        $olt = Olt::create([
            'name' => 'Test OLT',
            'ip_address' => '192.168.1.100',
            'port' => 23,
            'management_protocol' => 'telnet',
            'username' => 'admin',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Olt::class, $olt);
        $this->assertEquals('Test OLT', $olt->name);
        $this->assertEquals('192.168.1.100', $olt->ip_address);
    }

    public function test_olt_has_many_onus(): void
    {
        $olt = Olt::factory()->create();
        $onu1 = Onu::factory()->create(['olt_id' => $olt->id]);
        $onu2 = Onu::factory()->create(['olt_id' => $olt->id]);

        $this->assertCount(2, $olt->onus);
        $this->assertTrue($olt->onus->contains($onu1));
        $this->assertTrue($olt->onus->contains($onu2));
    }

    public function test_olt_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $olt = Olt::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $olt->tenant);
        $this->assertEquals($tenant->id, $olt->tenant->id);
    }

    public function test_olt_scope_active_returns_only_active_olts(): void
    {
        Olt::factory()->create(['status' => 'active']);
        Olt::factory()->create(['status' => 'inactive']);
        Olt::factory()->create(['status' => 'maintenance']);

        $activeOlts = Olt::active()->get();

        $this->assertCount(1, $activeOlts);
        $this->assertEquals('active', $activeOlts->first()->status);
    }

    public function test_olt_scope_inactive_returns_only_inactive_olts(): void
    {
        Olt::factory()->create(['status' => 'active']);
        Olt::factory()->create(['status' => 'inactive']);

        $inactiveOlts = Olt::inactive()->get();

        $this->assertCount(1, $inactiveOlts);
        $this->assertEquals('inactive', $inactiveOlts->first()->status);
    }

    public function test_olt_scope_maintenance_returns_only_maintenance_olts(): void
    {
        Olt::factory()->create(['status' => 'active']);
        Olt::factory()->create(['status' => 'maintenance']);

        $maintenanceOlts = Olt::maintenance()->get();

        $this->assertCount(1, $maintenanceOlts);
        $this->assertEquals('maintenance', $maintenanceOlts->first()->status);
    }

    public function test_olt_is_active_method_returns_true_for_active_olt(): void
    {
        $olt = Olt::factory()->create(['status' => 'active']);

        $this->assertTrue($olt->isActive());
    }

    public function test_olt_is_active_method_returns_false_for_inactive_olt(): void
    {
        $olt = Olt::factory()->create(['status' => 'inactive']);

        $this->assertFalse($olt->isActive());
    }

    public function test_olt_can_connect_method_returns_true_for_valid_olt(): void
    {
        $olt = Olt::factory()->create([
            'status' => 'active',
            'ip_address' => '192.168.1.100',
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $this->assertTrue($olt->canConnect());
    }

    public function test_olt_can_connect_method_returns_false_for_inactive_olt(): void
    {
        $olt = Olt::factory()->create([
            'status' => 'inactive',
            'ip_address' => '192.168.1.100',
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $this->assertFalse($olt->canConnect());
    }

    public function test_olt_credentials_are_encrypted(): void
    {
        $olt = Olt::factory()->create([
            'username' => 'testuser',
            'password' => 'testpass',
            'snmp_community' => 'public',
        ]);

        $this->assertDatabaseHas('olts', [
            'id' => $olt->id,
        ]);

        // Ensure credentials are not stored in plain text in the database
        $rawOlt = \DB::table('olts')->find($olt->id);
        $this->assertNotEquals('testuser', $rawOlt->username);
        $this->assertNotEquals('testpass', $rawOlt->password);
    }

    public function test_olt_hidden_attributes_are_not_in_array(): void
    {
        $olt = Olt::factory()->create([
            'username' => 'admin',
            'password' => 'password123',
            'snmp_community' => 'public',
        ]);

        $array = $olt->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('username', $array);
        $this->assertArrayNotHasKey('snmp_community', $array);
    }
}
