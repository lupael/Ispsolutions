<?php

namespace Tests\Feature;

use App\Http\Middleware\ResolveTenant;
use App\Models\Tenant;
use App\Services\TenancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenancyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private TenancyService $tenancyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenancyService = app(TenancyService::class);
    }

    public function test_middleware_resolves_tenant_by_domain(): void
    {
        $tenant = Tenant::factory()->create([
            'domain' => 'test-isp.com',
            'status' => 'active',
        ]);

        $request = Request::create('http://test-isp.com/dashboard', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($tenant->id, $this->tenancyService->getCurrentTenantId());
    }

    public function test_middleware_resolves_tenant_by_subdomain(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'test',
            'status' => 'active',
        ]);

        $request = Request::create('http://test.example.com/dashboard', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($tenant->id, $this->tenancyService->getCurrentTenantId());
    }

    public function test_middleware_returns_404_for_unknown_tenant(): void
    {
        $request = Request::create('http://unknown-tenant.com/dashboard', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Tenant not found');

        $middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    public function test_middleware_allows_public_routes_without_tenant(): void
    {
        $request = Request::create('http://unknown.com/login', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull($this->tenancyService->getCurrentTenantId());
    }

    public function test_middleware_does_not_resolve_inactive_tenant(): void
    {
        Tenant::factory()->create([
            'domain' => 'inactive-isp.com',
            'status' => 'inactive',
        ]);

        $request = Request::create('http://inactive-isp.com/dashboard', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($req) {
            return response('OK');
        });
    }
}
