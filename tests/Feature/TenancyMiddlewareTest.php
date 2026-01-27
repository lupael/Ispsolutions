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

    public function test_middleware_returns_404_for_unknown_tenant_on_non_panel_routes(): void
    {
        // Panel routes should work without tenant, but other routes should not
        $request = Request::create('http://unknown-tenant.com/some-other-route', 'GET');
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

    public function test_middleware_allows_panel_routes_without_tenant(): void
    {
        $panelRoutes = [
            'http://unknown.com/panel/admin/dashboard',
            'http://unknown.com/panel/operator/dashboard',
            'http://unknown.com/panel/manager/dashboard',
        ];

        $middleware = new ResolveTenant($this->tenancyService);

        foreach ($panelRoutes as $url) {
            $request = Request::create($url, 'GET');

            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });

            $this->assertEquals(200, $response->getStatusCode(), "Failed for URL: {$url}");
            $this->assertNull($this->tenancyService->getCurrentTenantId());
        }
    }

    public function test_middleware_resolves_tenant_for_panel_routes_when_host_matches(): void
    {
        $tenant = Tenant::factory()->create([
            'domain' => 'test-isp.com',
            'status' => 'active',
        ]);

        $panelRoutes = [
            'http://test-isp.com/panel/admin/dashboard',
            'http://test-isp.com/panel/operator/dashboard',
            'http://test-isp.com/panel/manager/dashboard',
        ];

        $middleware = new ResolveTenant($this->tenancyService);

        foreach ($panelRoutes as $url) {
            $request = Request::create($url, 'GET');

            $response = $middleware->handle($request, function ($req) {
                return response('OK');
            });

            $this->assertEquals(200, $response->getStatusCode(), "Failed for URL: {$url}");
            $this->assertEquals($tenant->id, $this->tenancyService->getCurrentTenantId(), "Tenant not resolved for URL: {$url}");
        }
    }

    public function test_middleware_does_not_resolve_inactive_tenant(): void
    {
        Tenant::factory()->create([
            'domain' => 'inactive-isp.com',
            'status' => 'inactive',
        ]);

        // For non-panel routes, inactive tenant should cause 404
        $request = Request::create('http://inactive-isp.com/some-other-route', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    public function test_middleware_allows_panel_routes_with_inactive_tenant(): void
    {
        Tenant::factory()->create([
            'domain' => 'inactive-isp.com',
            'status' => 'inactive',
        ]);

        // Panel routes should work even with inactive tenant (tenant won't be resolved)
        $request = Request::create('http://inactive-isp.com/panel/admin/dashboard', 'GET');
        $middleware = new ResolveTenant($this->tenancyService);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        // Tenant should not be resolved since it's inactive
        $this->assertNull($this->tenancyService->getCurrentTenantId());
    }

    public function test_middleware_resolves_tenant_from_authenticated_user_when_domain_fails(): void
    {
        $tenant = Tenant::factory()->create([
            'domain' => 'test-isp.com',
            'status' => 'active',
        ]);

        $user = \App\Models\User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        // Request from unknown domain but with authenticated user
        $request = Request::create('http://unknown.com/panel/admin/dashboard', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new ResolveTenant($this->tenancyService);

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        // Tenant should be resolved from authenticated user
        $this->assertEquals($tenant->id, $this->tenancyService->getCurrentTenantId());
    }
}
