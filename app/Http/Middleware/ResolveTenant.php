<?php

namespace App\Http\Middleware;

use App\Services\TenancyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        private readonly TenancyService $tenancyService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Resolve tenant by domain/subdomain
        $tenant = $this->tenancyService->resolveTenantByDomain($host);
        
        // Set current tenant
        $this->tenancyService->setCurrentTenant($tenant);
        
        // If tenant is required but not found, abort
        if (! $tenant && $this->requiresTenant($request)) {
            abort(404, 'Tenant not found');
        }

        return $next($request);
    }

    /**
     * Check if the current request requires a tenant.
     */
    private function requiresTenant(Request $request): bool
    {
        // Allow public routes without tenant
        $publicRoutes = [
            'api/v1/public/*',
            'login',
            'register',
            'health',
        ];

        foreach ($publicRoutes as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }
}
