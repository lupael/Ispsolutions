<?php

namespace Tests\Unit\Models;

use App\Models\NetworkUser;
use App\Models\Olt;
use App\Models\Onu;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnuTest extends TestCase
{
    use RefreshDatabase;

    public function test_onu_can_be_created(): void
    {
        $olt = Olt::factory()->create();
        
        $onu = Onu::create([
            'olt_id' => $olt->id,
            'pon_port' => '0/1/1',
            'onu_id' => 1,
            'serial_number' => 'HWTC12345678',
            'status' => 'online',
        ]);

        $this->assertInstanceOf(Onu::class, $onu);
        $this->assertEquals('0/1/1', $onu->pon_port);
        $this->assertEquals('HWTC12345678', $onu->serial_number);
    }

    public function test_onu_belongs_to_olt(): void
    {
        $olt = Olt::factory()->create();
        $onu = Onu::factory()->create(['olt_id' => $olt->id]);

        $this->assertInstanceOf(Olt::class, $onu->olt);
        $this->assertEquals($olt->id, $onu->olt->id);
    }

    public function test_onu_belongs_to_network_user(): void
    {
        $networkUser = NetworkUser::factory()->create();
        $onu = Onu::factory()->create(['network_user_id' => $networkUser->id]);

        $this->assertInstanceOf(NetworkUser::class, $onu->networkUser);
        $this->assertEquals($networkUser->id, $onu->networkUser->id);
    }

    public function test_onu_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $onu = Onu::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $onu->tenant);
        $this->assertEquals($tenant->id, $onu->tenant->id);
    }

    public function test_onu_scope_online_returns_only_online_onus(): void
    {
        Onu::factory()->create(['status' => 'online']);
        Onu::factory()->create(['status' => 'offline']);
        Onu::factory()->create(['status' => 'los']);

        $onlineOnus = Onu::online()->get();

        $this->assertCount(1, $onlineOnus);
        $this->assertEquals('online', $onlineOnus->first()->status);
    }

    public function test_onu_scope_offline_returns_only_offline_onus(): void
    {
        Onu::factory()->create(['status' => 'online']);
        Onu::factory()->create(['status' => 'offline']);

        $offlineOnus = Onu::offline()->get();

        $this->assertCount(1, $offlineOnus);
        $this->assertEquals('offline', $offlineOnus->first()->status);
    }

    public function test_onu_scope_by_olt_returns_onus_for_specific_olt(): void
    {
        $olt1 = Olt::factory()->create();
        $olt2 = Olt::factory()->create();
        
        Onu::factory()->create(['olt_id' => $olt1->id]);
        Onu::factory()->create(['olt_id' => $olt1->id]);
        Onu::factory()->create(['olt_id' => $olt2->id]);

        $olt1Onus = Onu::byOlt($olt1->id)->get();

        $this->assertCount(2, $olt1Onus);
        $olt1Onus->each(fn ($onu) => $this->assertEquals($olt1->id, $onu->olt_id));
    }

    public function test_onu_is_online_method_returns_true_for_online_onu(): void
    {
        $onu = Onu::factory()->create(['status' => 'online']);

        $this->assertTrue($onu->isOnline());
    }

    public function test_onu_is_online_method_returns_false_for_offline_onu(): void
    {
        $onu = Onu::factory()->create(['status' => 'offline']);

        $this->assertFalse($onu->isOnline());
    }

    public function test_onu_get_full_pon_path_returns_correct_format(): void
    {
        $olt = Olt::factory()->create(['name' => 'OLT-Main']);
        $onu = Onu::factory()->create([
            'olt_id' => $olt->id,
            'pon_port' => '0/1/1',
            'onu_id' => 5,
        ]);

        $path = $onu->getFullPonPath();

        $this->assertEquals('OLT-Main / 0/1/1 / 5', $path);
    }

    public function test_onu_get_full_pon_path_handles_missing_olt(): void
    {
        $olt = Olt::factory()->create(['name' => 'Test OLT']);
        $onu = Onu::factory()->create([
            'olt_id' => $olt->id,
            'pon_port' => '0/1/1',
            'onu_id' => 5,
        ]);

        // Delete the OLT to simulate missing relationship
        $olt->delete();

        // Refresh the ONU to clear the relationship cache
        $onu->refresh();
        
        $path = $onu->getFullPonPath();

        $this->assertEquals('Unknown OLT / 0/1/1 / 5', $path);
    }

    public function test_onu_signal_values_are_cast_correctly(): void
    {
        $onu = Onu::factory()->create([
            'signal_rx' => -25.50,
            'signal_tx' => 2.75,
        ]);

        $this->assertIsFloat($onu->signal_rx);
        $this->assertIsFloat($onu->signal_tx);
        $this->assertEquals(-25.50, $onu->signal_rx);
        $this->assertEquals(2.75, $onu->signal_tx);
    }

    public function test_onu_unique_constraint_on_olt_pon_onu_combination(): void
    {
        $olt = Olt::factory()->create();
        
        Onu::factory()->create([
            'olt_id' => $olt->id,
            'pon_port' => '0/1/1',
            'onu_id' => 1,
            'serial_number' => 'HWTC11111111',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Attempt to create duplicate ONU with same OLT, PON port, and ONU ID
        Onu::factory()->create([
            'olt_id' => $olt->id,
            'pon_port' => '0/1/1',
            'onu_id' => 1,
            'serial_number' => 'HWTC22222222',
        ]);
    }

    public function test_onu_serial_number_must_be_unique(): void
    {
        Onu::factory()->create(['serial_number' => 'HWTC12345678']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Onu::factory()->create(['serial_number' => 'HWTC12345678']);
    }
}
