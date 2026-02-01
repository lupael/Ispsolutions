<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\OltServiceInterface;
use App\Http\Controllers\Controller;
use App\Jobs\SyncOnusJob;
use App\Models\Olt;
use App\Models\OltSnmpTrap;
use App\Models\Onu;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OltController extends Controller
{
    public function __construct(
        private OltServiceInterface $oltService
    ) {}

    /**
     * Get list of OLTs with statistics
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Olt::class);

        // CRITICAL: Filter by tenant_id to prevent data leakage
        // Use null-safe operator to avoid errors when user is null
        $tenantId = auth()->user()?->tenant_id ?? getCurrentTenantId();
        $olts = Olt::where('tenant_id', $tenantId)->with('onus')->get()->map(function ($olt) {
            return [
                'id' => $olt->id,
                'name' => $olt->name,
                'ip_address' => $olt->ip_address,
                'model' => $olt->model,
                'status' => $olt->status,
                'health_status' => $olt->health_status,
                'total_onus' => $olt->onus->count(),
                'online_onus' => $olt->onus->where('status', 'online')->count(),
                'offline_onus' => $olt->onus->where('status', 'offline')->count(),
                'last_health_check_at' => $olt->last_health_check_at?->toISOString(),
                'last_backup_at' => $olt->last_backup_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $olts,
        ]);
    }

    /**
     * Get OLT details with ONUs
     */
    public function show(int $id): JsonResponse
    {
        // CRITICAL: Verify OLT belongs to current tenant
        // Use null-safe operator to avoid errors when user is null
        $tenantId = auth()->user()?->tenant_id ?? getCurrentTenantId();
        $olt = Olt::where('tenant_id', $tenantId)
            ->with(['onus' => function ($query) {
                $query->latest();
            }])
            ->findOrFail($id);

        $this->authorize('view', $olt);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $olt->id,
                'name' => $olt->name,
                'ip_address' => $olt->ip_address,
                'port' => $olt->port,
                'model' => $olt->model,
                'location' => $olt->location,
                'status' => $olt->status,
                'health_status' => $olt->health_status,
                'management_protocol' => $olt->management_protocol,
                'last_health_check_at' => $olt->last_health_check_at?->toISOString(),
                'last_backup_at' => $olt->last_backup_at?->toISOString(),
                'onus' => $olt->onus->map(function ($onu) {
                    return [
                        'id' => $onu->id,
                        'serial_number' => $onu->serial_number,
                        'pon_port' => $onu->pon_port,
                        'onu_id' => $onu->onu_id,
                        'status' => $onu->status,
                        'signal_rx' => $onu->signal_rx,
                        'signal_tx' => $onu->signal_tx,
                        'distance' => $onu->distance,
                        'last_seen_at' => $onu->last_seen_at?->toISOString(),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Test OLT connection
     */
    public function testConnection(int $id): JsonResponse
    {
        $result = $this->oltService->testConnection($id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Connection successful' : 'Connection failed',
            'latency' => $result['latency'] ?? null,
        ]);
    }

    /**
     * Sync ONUs from OLT (queued for async processing)
     */
    public function syncOnus(int $id): JsonResponse
    {
        try {
            // Verify OLT exists before queuing
            Olt::findOrFail($id);
            
            // Dispatch job to queue for async processing to avoid timeout
            SyncOnusJob::dispatch($id);
            
            Log::info("ONU sync job dispatched for OLT {$id}");
            
            return response()->json([
                'success' => true,
                'message' => 'ONU sync started in background. This may take several minutes for large OLTs.',
                'queued' => true,
            ]);
        } catch (ModelNotFoundException $e) {
            Log::warning("ONU sync requested for non-existent OLT {$id}");
            
            return response()->json([
                'success' => false,
                'message' => 'OLT not found.',
                'queued' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error("Failed to dispatch ONU sync job for OLT {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start sync operation. Please try again later.',
                'queued' => false,
            ], 500);
        }
    }

    /**
     * Get OLT statistics
     */
    public function statistics(int $id): JsonResponse
    {
        $stats = $this->oltService->getOltStatistics($id);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Create OLT backup
     */
    public function createBackup(int $id): JsonResponse
    {
        $success = $this->oltService->createBackup($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Backup created successfully' : 'Backup creation failed',
        ]);
    }

    /**
     * Get OLT backups
     */
    public function backups(int $id): JsonResponse
    {
        // CRITICAL: Verify OLT belongs to current tenant before accessing backups
        // Use null-safe operator to avoid errors when user is null
        $tenantId = auth()->user()?->tenant_id ?? getCurrentTenantId();
        $olt = Olt::where('tenant_id', $tenantId)->findOrFail($id);
        
        $backups = $this->oltService->getBackupList($id);

        return response()->json([
            'success' => true,
            'data' => $backups,
        ]);
    }

    /**
     * Get all backups across all OLTs
     */
    public function allBackups(): JsonResponse
    {
        $tenantId = getCurrentTenantId();
        
        // Use a single query with join for better performance
        $backups = \App\Models\OltBackup::join('olts', 'olt_backups.olt_id', '=', 'olts.id')
            ->where('olts.tenant_id', $tenantId)
            ->select([
                'olt_backups.id',
                'olt_backups.olt_id',
                'olts.name as olt_name',
                'olt_backups.file_size',
                'olt_backups.backup_type',
                'olt_backups.created_at',
            ])
            ->selectRaw('SUBSTRING_INDEX(olt_backups.file_path, "/", -1) as file_name')
            ->orderBy('olt_backups.created_at', 'desc')
            ->get()
            ->map(function ($backup) {
                return [
                    'id' => $backup->id,
                    'olt_id' => $backup->olt_id,
                    'olt_name' => $backup->olt_name,
                    'file_name' => $backup->file_name,
                    'file_size' => $backup->file_size,
                    'backup_type' => $backup->backup_type,
                    'created_at' => $backup->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $backups,
        ]);
    }

    /**
     * Get port utilization
     */
    public function portUtilization(int $id): JsonResponse
    {
        $utilization = $this->oltService->getPortUtilization($id);

        return response()->json([
            'success' => true,
            'data' => $utilization,
        ]);
    }

    /**
     * Get bandwidth usage
     */
    public function bandwidthUsage(int $id, Request $request): JsonResponse
    {
        $period = $request->input('period', 'daily');
        $usage = $this->oltService->getBandwidthUsage($id, $period);

        return response()->json([
            'success' => true,
            'data' => $usage,
        ]);
    }

    /**
     * Get ONU details
     */
    public function onuDetails(int $onuId): JsonResponse
    {
        $onu = Onu::with(['olt', 'networkUser'])->findOrFail($onuId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $onu->id,
                'serial_number' => $onu->serial_number,
                'mac_address' => $onu->mac_address,
                'pon_port' => $onu->pon_port,
                'onu_id' => $onu->onu_id,
                'name' => $onu->name,
                'description' => $onu->description,
                'status' => $onu->status,
                'signal_rx' => $onu->signal_rx,
                'signal_tx' => $onu->signal_tx,
                'distance' => $onu->distance,
                'ipaddress' => $onu->ipaddress,
                'last_seen_at' => $onu->last_seen_at?->toISOString(),
                'last_sync_at' => $onu->last_sync_at?->toISOString(),
                'olt' => [
                    'id' => $onu->olt->id,
                    'name' => $onu->olt->name,
                    'ip_address' => $onu->olt->ip_address,
                ],
                'network_user' => $onu->networkUser ? [
                    'id' => $onu->networkUser->id,
                    'username' => $onu->networkUser->username,
                    'name' => $onu->networkUser->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Refresh ONU status
     */
    public function refreshOnuStatus(int $onuId): JsonResponse
    {
        $success = $this->oltService->refreshOnuStatus($onuId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'ONU status refreshed' : 'Failed to refresh ONU status',
        ]);
    }

    /**
     * Authorize ONU
     */
    public function authorizeOnu(int $onuId): JsonResponse
    {
        $success = $this->oltService->authorizeOnu($onuId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'ONU authorized successfully' : 'Failed to authorize ONU',
        ]);
    }

    /**
     * Unauthorize ONU
     */
    public function unauthorizeOnu(int $onuId): JsonResponse
    {
        $success = $this->oltService->unauthorizeOnu($onuId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'ONU unauthorized successfully' : 'Failed to unauthorize ONU',
        ]);
    }

    /**
     * Reboot ONU
     */
    public function rebootOnu(int $onuId): JsonResponse
    {
        $success = $this->oltService->rebootOnu($onuId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'ONU reboot initiated' : 'Failed to reboot ONU',
        ]);
    }

    /**
     * Bulk ONU operations
     */
    public function bulkOnuOperations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onu_ids' => 'required|array',
            'onu_ids.*' => 'required|integer|exists:onus,id',
            'operation' => 'required|string|in:authorize,unauthorize,reboot,refresh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $onuIds = $request->input('onu_ids');
        $operation = $request->input('operation');
        $results = [];
        $successCount = 0;

        foreach ($onuIds as $onuId) {
            $success = match ($operation) {
                'authorize' => $this->oltService->authorizeOnu($onuId),
                'unauthorize' => $this->oltService->unauthorizeOnu($onuId),
                'reboot' => $this->oltService->rebootOnu($onuId),
                'refresh' => $this->oltService->refreshOnuStatus($onuId),
                default => false,
            };

            $results[] = [
                'onu_id' => $onuId,
                'success' => $success,
            ];

            if ($success) {
                $successCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Operation completed: {$successCount} of " . count($onuIds) . ' successful',
            'results' => $results,
        ]);
    }

    /**
     * Get real-time ONU monitoring data
     */
    public function monitorOnus(int $id): JsonResponse
    {
        // CRITICAL: Verify OLT belongs to current tenant
        // Use null-safe operator to avoid errors when user is null
        $tenantId = auth()->user()?->tenant_id ?? getCurrentTenantId();
        $olt = Olt::where('tenant_id', $tenantId)->findOrFail($id);
        
        // Use relationship to ensure ONUs are properly scoped to this OLT and tenant
        $onus = $olt->onus()
            ->where('tenant_id', $tenantId)
            ->orderBy('status', 'desc')
            ->orderBy('pon_port')
            ->orderBy('onu_id')
            ->get()
            ->map(function ($onu) {
                return [
                    'id' => $onu->id,
                    'serial_number' => $onu->serial_number,
                    'pon_port' => $onu->pon_port,
                    'onu_id' => $onu->onu_id,
                    'status' => $onu->status,
                    'signal_rx' => $onu->signal_rx,
                    'signal_tx' => $onu->signal_tx,
                    'distance' => $onu->distance,
                    'last_seen_at' => $onu->last_seen_at?->toISOString(),
                    'signal_quality' => $this->calculateSignalQuality($onu->signal_rx),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'olt' => [
                    'id' => $olt->id,
                    'name' => $olt->name,
                    'status' => $olt->status,
                ],
                'onus' => $onus,
                'summary' => [
                    'total' => $onus->count(),
                    'online' => $onus->where('status', 'online')->count(),
                    'offline' => $onus->where('status', 'offline')->count(),
                    'average_signal_rx' => ($avgSignal = $onus->whereNotNull('signal_rx')->avg('signal_rx')) !== null
                        ? round($avgSignal, 2)
                        : null,
                ],
            ],
        ]);
    }

    /**
     * Calculate signal quality from signal strength
     */
    private function calculateSignalQuality(?float $signalRx): string
    {
        if ($signalRx === null) {
            return 'unknown';
        }

        // Signal strength is typically in dBm (negative values)
        if ($signalRx >= -23) {
            return 'excellent';
        } elseif ($signalRx >= -25) {
            return 'good';
        } elseif ($signalRx >= -27) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get SNMP traps for all OLTs or a specific OLT
     */
    public function snmpTraps(Request $request): JsonResponse
    {
        $tenantId = getCurrentTenantId();
        
        $query = OltSnmpTrap::with(['olt:id,name', 'acknowledgedByUser:id,name'])
            ->whereHas('olt', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('created_at', 'desc');

        // Filter by OLT if specified
        if ($oltId = $request->input('olt_id')) {
            $query->where('olt_id', $oltId);
        }

        // Filter by severity if specified
        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        // Filter by acknowledged status if specified
        if ($request->has('acknowledged')) {
            $acknowledged = filter_var($request->input('acknowledged'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_acknowledged', $acknowledged);
        }

        $traps = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $traps->items(),
            'pagination' => [
                'current_page' => $traps->currentPage(),
                'last_page' => $traps->lastPage(),
                'per_page' => $traps->perPage(),
                'total' => $traps->total(),
            ],
        ]);
    }

    /**
     * Acknowledge a specific SNMP trap
     */
    public function acknowledgeTrap(int $trapId): JsonResponse
    {
        $tenantId = getCurrentTenantId();
        
        $trap = OltSnmpTrap::whereHas('olt', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->findOrFail($trapId);
        
        $trap->acknowledge(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Trap acknowledged successfully',
        ]);
    }

    /**
     * Acknowledge all unacknowledged SNMP traps
     */
    public function acknowledgeAllTraps(Request $request): JsonResponse
    {
        $tenantId = getCurrentTenantId();
        
        $query = OltSnmpTrap::unacknowledged()
            ->whereHas('olt', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

        // Optionally filter by OLT
        if ($oltId = $request->input('olt_id')) {
            $query->where('olt_id', $oltId);
        }

        // Optionally filter by severity
        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        $count = $query->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'acknowledged_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Acknowledged {$count} traps",
            'count' => $count,
        ]);
    }
}
