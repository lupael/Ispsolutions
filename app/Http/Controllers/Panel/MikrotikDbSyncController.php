<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CustomerImport;
use App\Models\MikrotikRouter;
use App\Models\MikrotikIpPool;
use App\Models\MikrotikProfile;
use App\Models\MikrotikPppSecret;
use App\Services\RouterosAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * MikrotikDbSyncController - Import/sync data from MikroTik routers
 * 
 * Following IspBills pattern for mikrotik_db_sync operations
 */
class MikrotikDbSyncController extends Controller
{
    /**
     * Display import interface
     */
    public function index(): View
    {
        $routers = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->get();
        
        $recentImports = CustomerImport::with('router', 'nas')
            ->where('tenant_id', getCurrentTenantId())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('panels.admin.network.router-import', compact('routers', 'recentImports'));
    }

    /**
     * Import IP pools from router (IspBills pattern)
     */
    public function importIpPools(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'timeout' => (int) config('services.mikrotik.timeout', 30),
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to router',
            ], 500);
        }

        try {
            // Delete old imported pools for this router
            MikrotikIpPool::where('router_id', $router->id)->delete();

            // Fetch IP pools from router
            $ip4pools = $api->getMktRows('ip_pool');
            
            $importedCount = 0;
            foreach ($ip4pools as $ip4pool) {
                $ranges = $this->parseIpPool($ip4pool['ranges'] ?? '');
                
                if (empty($ranges)) {
                    continue;
                }

                MikrotikIpPool::create([
                    'router_id' => $router->id,
                    'name' => $ip4pool['name'] ?? 'unnamed',
                    'ranges' => $ranges,
                ]);
                
                $importedCount++;
            }

            $api->disconnect();

            Log::info('IP pools imported from router', [
                'router_id' => $router->id,
                'imported_count' => $importedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Imported {$importedCount} IP pools successfully",
                'imported_count' => $importedCount,
            ]);
        } catch (\Exception $e) {
            $api->disconnect();
            
            Log::error('Failed to import IP pools', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import IP pools: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import PPP profiles from router (IspBills pattern)
     */
    public function importPppProfiles(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'timeout' => (int) config('services.mikrotik.timeout', 30),
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to router',
            ], 500);
        }

        try {
            // Delete old imported profiles for this router and tenant
            MikrotikProfile::where('router_id', $router->id)
                ->where('tenant_id', getCurrentTenantId())
                ->delete();

            // Fetch non-default PPP profiles from router
            $pppProfiles = $api->getMktRows('ppp_profile', ['default' => 'no']);
            
            $importedCount = 0;
            foreach ($pppProfiles as $pppProfile) {
                MikrotikProfile::create([
                    'tenant_id' => getCurrentTenantId(),
                    'router_id' => $router->id,
                    'name' => $pppProfile['name'] ?? 'unnamed',
                    'local_address' => $pppProfile['local-address'] ?? '',
                    'remote_address' => $pppProfile['remote-address'] ?? '',
                    'rate_limit' => $pppProfile['rate-limit'] ?? null,
                    'session_timeout' => $pppProfile['session-timeout'] ?? null,
                    'idle_timeout' => $pppProfile['idle-timeout'] ?? null,
                ]);
                
                $importedCount++;
            }

            $api->disconnect();

            Log::info('PPP profiles imported from router', [
                'router_id' => $router->id,
                'imported_count' => $importedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Imported {$importedCount} PPP profiles successfully",
                'imported_count' => $importedCount,
            ]);
        } catch (\Exception $e) {
            $api->disconnect();
            
            Log::error('Failed to import PPP profiles', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import PPP profiles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import PPP secrets from router (IspBills pattern)
     * 
     * Creates router-side backup before importing
     */
    public function importPppSecrets(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::with('nas')
            ->where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $validated = $request->validate([
            'import_disabled_user' => 'nullable|in:yes,no',
        ]);

        $importDisabledUser = $validated['import_disabled_user'] ?? 'no';

        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'timeout' => (int) config('services.mikrotik.timeout', 30),
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot connect to router',
            ], 500);
        }

        DB::beginTransaction();
        try {
            // Step 1: Create router-side export backup before importing (IspBills pattern)
            $file = 'ppp-secret-backup-by-billing-' . now()->timestamp;
            $backupResult = $api->ttyWrite('/ppp/secret/export', ['file' => $file]);
            
            // Check if backup succeeded
            if ($backupResult === null) {
                throw new \RuntimeException('Failed to create router-side backup before import');
            }
            
            // Step 2: Create import request record
            $customerImport = CustomerImport::create([
                'tenant_id' => getCurrentTenantId(),
                'operator_id' => auth()->id(),
                'nas_id' => $router->nas_id,
                'router_id' => $router->id,
                'status' => CustomerImport::STATUS_IN_PROGRESS,
                'options' => [
                    'import_disabled_user' => $importDisabledUser,
                    'backup_file' => $file,
                ],
            ]);
            
            // Step 3: Delete old imported secrets for this router and tenant
            MikrotikPppSecret::where('router_id', $router->id)
                ->where('tenant_id', getCurrentTenantId())
                ->delete();

            // Step 4: Fetch PPP secrets from router
            $query = ($importDisabledUser === 'no') ? ['disabled' => 'no'] : [];
            $secrets = $api->getMktRows('ppp_secret', $query);
            
            $customerImport->update(['total_count' => count($secrets)]);
            
            $importedCount = 0;
            $failedCount = 0;
            $errors = [];
            
            foreach ($secrets as $secret) {
                try {
                    MikrotikPppSecret::create([
                        'tenant_id' => getCurrentTenantId(),
                        'customer_import_id' => $customerImport->id,
                        'operator_id' => auth()->id(),
                        'nas_id' => $router->nas_id,
                        'router_id' => $router->id,
                        'name' => $secret['name'] ?? '',
                        'password' => $secret['password'] ?? '',
                        'profile' => $secret['profile'] ?? '',
                        'remote_address' => $secret['remote-address'] ?? null,
                        'disabled' => $secret['disabled'] ?? 'no',
                        'comment' => $secret['comment'] ?? null,
                    ]);
                    
                    $importedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'name' => $secret['name'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Update import status
            $customerImport->update([
                'status' => $failedCount > 0 ? CustomerImport::STATUS_FAILED : CustomerImport::STATUS_COMPLETED,
                'success_count' => $importedCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'completed_at' => now(),
            ]);

            DB::commit();
            $api->disconnect();

            Log::info('PPP secrets imported from router', [
                'router_id' => $router->id,
                'import_id' => $customerImport->id,
                'imported_count' => $importedCount,
                'failed_count' => $failedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Imported {$importedCount} PPP secrets successfully",
                'import_id' => $customerImport->id,
                'imported_count' => $importedCount,
                'failed_count' => $failedCount,
                'backup_file' => $file,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $api->disconnect();
            
            Log::error('Failed to import PPP secrets', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import PPP secrets: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import all data from router (IP pools, profiles, secrets)
     */
    public function importAll(Request $request, int $routerId): JsonResponse
    {
        $results = [
            'ip_pools' => $this->importIpPools($request, $routerId)->getData(true),
            'ppp_profiles' => $this->importPppProfiles($request, $routerId)->getData(true),
            'ppp_secrets' => $this->importPppSecrets($request, $routerId)->getData(true),
        ];

        $allSuccess = $results['ip_pools']['success'] 
            && $results['ppp_profiles']['success'] 
            && $results['ppp_secrets']['success'];

        return response()->json([
            'success' => $allSuccess,
            'message' => $allSuccess ? 'All data imported successfully' : 'Some imports failed',
            'results' => $results,
        ]);
    }

    /**
     * Parse IP pool ranges from MikroTik format and normalize as an array.
     * 
     * Supports:
     * - CIDR: 192.168.1.0/24
     * - Hyphen range: 192.168.1.1-192.168.1.254
     * - Comma-separated: 192.168.1.1,192.168.1.2
     * 
     * @param string $ranges Raw ranges string from MikroTik
     * @return array<int,string> Normalized array of range strings
     */
    private function parseIpPool(string $ranges): array
    {
        // Treat empty or whitespace-only input as no ranges
        if (trim($ranges) === '') {
            return [];
        }

        // Split on commas and trim each part to normalize the ranges
        $parts = explode(',', $ranges);
        $parts = array_map('trim', $parts);

        // Filter out any empty segments and reindex the array
        $normalized = array_values(
            array_filter(
                $parts,
                static function (string $part): bool {
                    return $part !== '';
                }
            )
        );

        return $normalized;
    }
}
