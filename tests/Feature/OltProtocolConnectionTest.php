<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Olt;
use App\Services\OltService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltProtocolConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_snmp_connection_returns_array(): void
    {
        $olt = Olt::factory()->create([
            'management_protocol' => 'snmp',
            'snmp_community' => 'public',
            'snmp_port' => 161,
            'ip_address' => '127.0.0.1', // in CI tests you should mock SNMP or run against test SNMP agent
        ]);

        $service = app(OltService::class);

        $result = $service->testConnection($olt->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_ssh_connection_returns_array(): void
    {
        $olt = Olt::factory()->create([
            'management_protocol' => 'ssh',
            'port' => 22,
            'username' => 'admin',
            'password' => 'password',
        ]);

        $service = app(OltService::class);

        $result = $service->testConnection($olt->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_telnet_connection_returns_array(): void
    {
        $olt = Olt::factory()->create([
            'management_protocol' => 'telnet',
            'port' => 23,
        ]);

        $service = app(OltService::class);

        $result = $service->testConnection($olt->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
}