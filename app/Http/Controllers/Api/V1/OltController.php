<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\OltServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $olts = Olt::with('onus')->get()->map(function ($olt) {
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
        $olt = Olt::with(['onus' => function ($query) {
            $query->latest();
        }])->findOrFail($id);

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
     * Sync ONUs from OLT
     */
    public function syncOnus(int $id): JsonResponse
    {
        $count = $this->oltService->syncOnus($id);

        return response()->json([
            'success' => $count !== null,
            'message' => $count !== null ? "Synced {$count} ONUs" : 'Sync failed',
            'count' => $count,
        ]);
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
        $backups = $this->oltService->getBackupList($id);

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
        $olt = Olt::findOrFail($id);
        $onus = Onu::where('olt_id', $id)
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
                    'average_signal_rx' => round($onus->whereNotNull('signal_rx')->avg('signal_rx'), 2),
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
}
