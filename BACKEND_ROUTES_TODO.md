# Backend Routes Required for New Features

This document outlines the backend routes that need to be implemented to fully enable the new Router Management Dashboard and IP Pool Analytics features.

## Router Management Dashboard

### 1. Test Router Connection
**Route:** `POST /api/routers/{id}/test`  
**Controller:** `App\Http\Controllers\Api\RouterController@testConnection`

**Implementation:**
```php
public function testConnection(MikrotikRouter $router): JsonResponse
{
    try {
        // Test connection to router via API/SSH
        $startTime = microtime(true);
        $connected = $router->testConnectivity();
        $latency = round((microtime(true) - $startTime) * 1000, 2);
        
        if ($connected) {
            return response()->json([
                'success' => true,
                'latency' => $latency,
                'message' => 'Connection successful'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Unable to connect to router'
        ], 503);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 2. Reconnect Router
**Route:** `POST /api/routers/{id}/reconnect`  
**Controller:** `App\Http\Controllers\Api\RouterController@reconnect`

**Implementation:**
```php
public function reconnect(MikrotikRouter $router): JsonResponse
{
    try {
        // Attempt to re-establish connection
        $router->disconnect();
        sleep(2); // Brief delay
        $connected = $router->connect();
        
        if ($connected) {
            $router->update(['status' => 'online', 'last_seen' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Router reconnected successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to reconnect to router'
        ], 503);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
```

**Route Registration (routes/api.php):**
```php
Route::middleware(['auth:sanctum', 'tenant'])->prefix('routers')->group(function () {
    Route::post('{router}/test', [RouterController::class, 'testConnection'])
        ->name('api.routers.test');
    Route::post('{router}/reconnect', [RouterController::class, 'reconnect'])
        ->name('api.routers.reconnect');
});
```

## IP Pool Analytics

### 3. Export IP Analytics
**Route:** `GET /panel/admin/network/ip-analytics/export`  
**Controller:** `App\Http\Controllers\Panel\Admin\NetworkController@exportIpAnalytics`

**Implementation:**
```php
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

public function exportIpAnalytics(Request $request)
{
    $format = $request->get('format', 'pdf');
    
    // Gather analytics data
    $analytics = $this->getIpPoolAnalytics();
    $poolStats = $this->getPoolStats();
    $recentAllocations = $this->getRecentAllocations();
    
    $data = compact('analytics', 'poolStats', 'recentAllocations');
    
    switch ($format) {
        case 'pdf':
            $pdf = Pdf::loadView('exports.ip-analytics-pdf', $data);
            return $pdf->download('ip-pool-analytics-' . date('Y-m-d') . '.pdf');
            
        case 'excel':
            return Excel::download(
                new IpAnalyticsExport($data),
                'ip-pool-analytics-' . date('Y-m-d') . '.xlsx'
            );
            
        case 'csv':
            return Excel::download(
                new IpAnalyticsExport($data),
                'ip-pool-analytics-' . date('Y-m-d') . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            );
            
        default:
            abort(400, 'Invalid export format');
    }
}

protected function getIpPoolAnalytics(): array
{
    $pools = IpPool::all();
    
    $totalIps = $pools->sum('total_ips');
    $allocatedIps = $pools->sum('used_ips');
    $availableIps = $totalIps - $allocatedIps;
    
    return [
        'total_ips' => $totalIps,
        'allocated_ips' => $allocatedIps,
        'available_ips' => $availableIps,
        'allocation_percent' => $totalIps > 0 ? ($allocatedIps / $totalIps) * 100 : 0,
        'available_percent' => $totalIps > 0 ? ($availableIps / $totalIps) * 100 : 0,
        'total_pools' => $pools->count(),
        'by_type' => $this->getPoolsByType($pools),
        'top_utilized' => $this->getTopUtilizedPools($pools),
    ];
}
```

**Route Registration (routes/web.php):**
```php
Route::middleware(['auth', 'tenant'])->prefix('panel/admin/network')->group(function () {
    Route::get('ip-analytics', [NetworkController::class, 'ipAnalytics'])
        ->name('panel.admin.network.ip-analytics');
    Route::get('ip-analytics/export', [NetworkController::class, 'exportIpAnalytics'])
        ->name('panel.admin.network.ip-analytics.export');
});
```

## Additional Model Methods Required

### MikrotikRouter Model
Add these methods to support the router dashboard:

```php
public function testConnectivity(): bool
{
    try {
        // Use MikroTik API to test connection
        $client = $this->getApiClient();
        return $client->connect();
    } catch (\Exception $e) {
        Log::error("Router connectivity test failed: " . $e->getMessage());
        return false;
    }
}

public function disconnect(): void
{
    $this->update(['status' => 'offline']);
    // Close any open connections
}

public function connect(): bool
{
    try {
        $client = $this->getApiClient();
        $connected = $client->connect();
        
        if ($connected) {
            $this->refreshStats();
            return true;
        }
        
        return false;
    } catch (\Exception $e) {
        Log::error("Router connection failed: " . $e->getMessage());
        return false;
    }
}

public function refreshStats(): void
{
    try {
        $client = $this->getApiClient();
        $resources = $client->read('/system/resource');
        
        if (!empty($resources[0])) {
            $this->update([
                'cpu_usage' => $resources[0]['cpu-load'] ?? 0,
                'memory_usage' => isset($resources[0]['free-memory'], $resources[0]['total-memory']) 
                    ? (($resources[0]['total-memory'] - $resources[0]['free-memory']) / $resources[0]['total-memory']) * 100 
                    : 0,
                'uptime' => $resources[0]['uptime'] ?? null,
                'status' => 'online',
                'last_seen' => now(),
            ]);
        }
    } catch (\Exception $e) {
        Log::error("Failed to refresh router stats: " . $e->getMessage());
    }
}
```

## Export Classes

Create these export classes for IP analytics:

### IpAnalyticsExport.php
```php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IpAnalyticsExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        
        foreach ($this->data['poolStats'] as $pool) {
            $rows[] = [
                $pool['name'],
                $pool['start_ip'],
                $pool['end_ip'],
                $pool['total_ips'],
                $pool['allocated_ips'],
                $pool['available_ips'],
                number_format($pool['utilization_percent'], 2) . '%',
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Pool Name',
            'Start IP',
            'End IP',
            'Total IPs',
            'Allocated',
            'Available',
            'Utilization %',
        ];
    }
}
```

## Summary

To fully enable the new features:

1. **Create RouterController** in `App\Http\Controllers\Api\`
2. **Add router API routes** in `routes/api.php`
3. **Add export methods** to NetworkController
4. **Add export route** in `routes/web.php`
5. **Create IpAnalyticsExport** class
6. **Create PDF export view** at `resources/views/exports/ip-analytics-pdf.blade.php`
7. **Add model methods** to MikrotikRouter for connectivity testing

All frontend views are complete and functional, showing appropriate error messages when backend routes are not yet implemented.
