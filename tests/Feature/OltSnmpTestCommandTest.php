<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\SnmpClientInterface;
use App\Models\Olt;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OltSnmpTestCommandTest extends TestCase
{
    public function test_command_reports_vsol_enterprise_table_when_present(): void
    {
        $olt = Olt::create([
            'name' => 'V-SOL OLT',
            'ip_address' => '192.168.200.2',
            'snmp_community' => 'public',
            'snmp_version' => 'v2c',
        ]);

        $mock = $this->createMock(SnmpClientInterface::class);

        // Expect walk called for EPON then FD-OLT table; EPON returns empty, FD-OLT returns entries
        $mock->expects($this->atLeast(1))
            ->method('walk')
            ->willReturnCallback(function ($passedOlt, $oid) {
                if (str_contains($oid, '.1.3.6.1.2.1.155')) {
                    return [];
                }

                if (str_contains($oid, '.1.3.6.1.4.1.11606')) {
                    return ['1' => 'VSOL-ONU-1'];
                }

                return [];
            });

        $this->instance(SnmpClientInterface::class, $mock);

        $output = Artisan::call('olt:snmp-test', ['--olt-id' => $olt->id]);

        $this->assertEquals(0, $output);
        $this->assertStringContainsString('FD-OLT: nmsEponOltPonTable', Artisan::output());
        $this->assertStringContainsString('[FOUND]', Artisan::output());
    }
}
