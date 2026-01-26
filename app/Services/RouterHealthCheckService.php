<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RouterHealthCheckService
{
    /**
     * Check the health of a single router
     */
    public function checkRouter(MikrotikRouter $router): array
    {
        $startTime = microtime(true);
        $status = 'unknown';
        $error = null;
        $responseTime = null;
        
        try {
            // Use existing MikrotikService to test connection
            $mikrotikService = app(MikrotikService::class);
            
            // Try to get active sessions as a health check
            $sessions = $mikrotikService->getActiveSessions($router->id);
            
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            
            // Connection successful if we got a response
            if ($sessions !== null) {
                $status = $responseTime < 1000 ? 'online' : 'warning';
            } else {
                $status = 'offline';
                $error = 'Failed to retrieve sessions from router';
            }
        } catch (\Exception $e) {
            $status = 'offline';
            $error = $e->getMessage();
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            
            Log::warning("Router health check failed for {$router->name}", [
                'router_id' => $router->id,
                'error' => $error,
            ]);
        }
        
        // Update router status
        $router->update([
            'api_status' => $status,
            'last_checked_at' => Carbon::now(),
            'last_error' => $error,
            'response_time_ms' => $responseTime,
        ]);
        
        return [
            'status' => $status,
            'response_time' => $responseTime,
            'error' => $error,
            'checked_at' => Carbon::now(),
        ];
    }
    
    /**
     * Check health of all routers for a tenant
     */
    public function checkAllRouters(?int $tenantId = null): array
    {
        $query = MikrotikRouter::query();
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        $routers = $query->get();
        $results = [];
        
        foreach ($routers as $router) {
            $results[$router->id] = $this->checkRouter($router);
        }
        
        return $results;
    }
    
    /**
     * Create base query for router statistics.
     */
    private function createBaseQuery(?int $tenantId = null)
    {
        $query = MikrotikRouter::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    /**
     * Get status statistics for routers
     */
    public function getStatusStatistics(?int $tenantId = null): array
    {
        return [
            'online' => $this->createBaseQuery($tenantId)->where('api_status', 'online')->count(),
            'offline' => $this->createBaseQuery($tenantId)->where('api_status', 'offline')->count(),
            'warning' => $this->createBaseQuery($tenantId)->where('api_status', 'warning')->count(),
            'unknown' => $this->createBaseQuery($tenantId)->where('api_status', 'unknown')->count(),
            'total' => $this->createBaseQuery($tenantId)->count(),
        ];
    }
    
    /**
     * Get status badge color class
     */
    public function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            'online' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
            'offline' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
            'unknown' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    /**
     * Get status icon
     */
    public function getStatusIcon(string $status): string
    {
        return match($status) {
            'online' => '●',  // Green dot
            'offline' => '●', // Red dot
            'warning' => '●', // Yellow dot
            'unknown' => '○', // Empty circle
            default => '○',
        };
    }
}
