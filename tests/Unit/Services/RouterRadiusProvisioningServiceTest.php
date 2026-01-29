<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\MikrotikRouter;
use App\Models\Nas;
use App\Services\MikrotikApiService;
use App\Services\RouterRadiusProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterRadiusProvisioningServiceTest extends TestCase
{
    use RefreshDatabase;

    private RouterRadiusProvisioningService $service;

    private MikrotikApiService $apiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiService = $this->createMock(MikrotikApiService::class);
        $this->service = new RouterRadiusProvisioningService($this->apiService);
    }

    public function test_provision_on_first_connect_requires_nas(): void
    {
        $router = MikrotikRouter::factory()->create([
            'nas_id' => null,
        ]);

        // Manually set nas to null to simulate missing NAS
        $router->setRelation('nas', null);

        $result = $this->service->provisionOnFirstConnect($router);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('NAS device', $result['message']);
    }

    public function test_provision_on_first_connect_success(): void
    {
        $nas = Nas::factory()->create([
            'server' => '192.168.1.1',
            'secret' => 'test-secret',
        ]);

        $router = MikrotikRouter::factory()->create([
            'nas_id' => $nas->id,
        ]);

        // Mock successful API calls
        // First getMktRows for checking existing RADIUS client
        $this->apiService->expects($this->once())
            ->method('getMktRows')
            ->willReturn([]);

        // One addMktRows for RADIUS client
        $this->apiService->expects($this->once())
            ->method('addMktRows')
            ->willReturn(['success' => true]);

        // Three ttyWrite calls for PPP AAA, RADIUS incoming, and backup
        $this->apiService->expects($this->exactly(3))
            ->method('ttyWrite')
            ->willReturn(['status' => 'success']);

        $result = $this->service->provisionOnFirstConnect($router);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('steps', $result);
        $this->assertTrue($result['steps']['radius_client']);
        $this->assertTrue($result['steps']['ppp_aaa']);
        $this->assertTrue($result['steps']['radius_incoming']);
    }

    public function test_provision_on_first_connect_partial_failure(): void
    {
        $nas = Nas::factory()->create([
            'server' => '192.168.1.1',
            'secret' => 'test-secret',
        ]);

        $router = MikrotikRouter::factory()->create([
            'nas_id' => $nas->id,
        ]);

        // Mock API calls where RADIUS client succeeds but PPP AAA fails
        $this->apiService->expects($this->once())
            ->method('getMktRows')
            ->willReturn([]);

        $this->apiService->expects($this->once())
            ->method('addMktRows')
            ->willReturn(['success' => true]);

        $this->apiService->expects($this->exactly(3))
            ->method('ttyWrite')
            ->willReturn(null); // Simulating failure

        $result = $this->service->provisionOnFirstConnect($router);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('failures', $result['message']);
    }

    public function test_provision_updates_existing_radius_client(): void
    {
        $nas = Nas::factory()->create([
            'server' => '192.168.1.1',
            'secret' => 'test-secret',
        ]);

        $router = MikrotikRouter::factory()->create([
            'nas_id' => $nas->id,
        ]);

        // Mock existing RADIUS client
        $this->apiService->expects($this->once())
            ->method('getMktRows')
            ->willReturn([['address' => '192.168.1.1', '.id' => '*1']]);

        $this->apiService->expects($this->once())
            ->method('editMktRow')
            ->willReturn(true);

        $this->apiService->expects($this->exactly(3))
            ->method('ttyWrite')
            ->willReturn(['status' => 'success']);

        $result = $this->service->provisionOnFirstConnect($router);

        $this->assertTrue($result['success']);
    }

    public function test_export_ppp_secrets_success(): void
    {
        $router = MikrotikRouter::factory()->create();

        $this->apiService->expects($this->once())
            ->method('ttyWrite')
            ->with($router, '/ppp/secret/export', $this->anything())
            ->willReturn(['status' => 'success']);

        $result = $this->service->exportPppSecrets($router);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('filename', $result);
        $this->assertStringContainsString('ppp-secret-backup-by-billing', $result['filename']);
    }

    public function test_export_ppp_secrets_failure(): void
    {
        $router = MikrotikRouter::factory()->create();

        $this->apiService->expects($this->once())
            ->method('ttyWrite')
            ->willReturn(null);

        $result = $this->service->exportPppSecrets($router);

        $this->assertFalse($result['success']);
    }

    public function test_provision_associates_router_with_nas(): void
    {
        $nas = Nas::factory()->create([
            'server' => '192.168.1.1',
            'secret' => 'test-secret',
        ]);

        $router = MikrotikRouter::factory()->create([
            'nas_id' => null, // Not associated yet
        ]);

        // Temporarily set nas_id for the test
        $router->nas_id = $nas->id;

        $this->apiService->method('getMktRows')->willReturn([]);
        $this->apiService->method('addMktRows')->willReturn(['success' => true]);
        $this->apiService->method('ttyWrite')->willReturn(['status' => 'success']);

        $result = $this->service->provisionOnFirstConnect($router);

        $this->assertTrue($result['steps']['nas_table']);
    }
}
