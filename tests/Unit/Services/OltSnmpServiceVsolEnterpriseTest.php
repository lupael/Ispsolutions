<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\SnmpClientInterface;
use App\Models\Olt;
use App\Services\OltSnmpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltSnmpServiceVsolEnterpriseTest extends TestCase
{
    use RefreshDatabase;

    public function test_vsol_enterprise_nms_epon_table_is_used_when_present(): void
    {
        // Create an OLT with SNMP configured and brand vsol
        $olt = Olt::create([
            'name' => 'V-SOL OLT',
            'ip_address' => '192.168.200.2',
            'port' => 22,
            'management_protocol' => 'snmp',
            'username' => 'admin',
            'password' => 'password',
            'snmp_community' => 'public',
            'snmp_version' => 'v2c',
            'snmp_port' => 161,
            'brand' => 'vsol',
            'status' => 'active',
            'health_status' => 'healthy',
        ]);

        // Mock SNMP client
        $mock = $this->createMock(SnmpClientInterface::class);

        // First walk (EPON standard) returns empty, second should be the V-SOL enterprise table
        $mock->expects($this->exactly(2))
            ->method('walk')
            ->withConsecutive(
                [$this->isInstanceOf(Olt::class), $this->stringContains('.1.3.6.1.2.1.155')],
                [$this->isInstanceOf(Olt::class), $this->stringContains('.1.3.6.1.4.1.11606')]
            )
            ->willReturnOnConsecutiveCalls([], [
                '1' => 'STRING: VSOLSER0001',
                '2' => 'STRING: VSOLSER0002',
            ]);

        $mock->method('get')->willReturnCallback(function ($passedOlt, $oid) {
            if (str_contains($oid, 'onu_status')) {
                return '1';
            }

            if (str_contains($oid, 'onu_rx_power')) {
                return '-3200';
            }

            if (str_contains($oid, 'onu_tx_power')) {
                return '-2600';
            }

            if (str_contains($oid, 'onu_distance')) {
                return '80';
            }

            return null;
        });

        $this->instance(SnmpClientInterface::class, $mock);

        /** @var OltSnmpService $service */
        $service = app(OltSnmpService::class);

        $result = $service->discoverOnusViaSNMP($olt);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals('VSOLSER0001', $result[0]['serial_number']);
        $this->assertEquals('online', $result[0]['status']);
        $this->assertEquals(-32.0, $result[0]['signal_rx']);
        $this->assertEquals(-26.0, $result[0]['signal_tx']);
        $this->assertEquals(80, $result[0]['distance']);
    }
}
