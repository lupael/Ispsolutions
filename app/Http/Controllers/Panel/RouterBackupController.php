<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationBackup;
use App\Services\RouterBackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RouterBackupController extends Controller
{
    public function __construct(
        private RouterBackupService $backupService
    ) {}

    /**
     * Display router backup management interface.
     */
    public function index(): View
    {
        $routers = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->orderBy('name')
            ->get();

        return view('panels.admin.routers.backup.index', compact('routers'));
    }

    /**
     * Show backups for a specific router.
     */
    public function show(int $routerId): View
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $backups = $this->backupService->listBackups($router);

        return view('panels.admin.routers.backup.show', compact('router', 'backups'));
    }

    /**
     * Create a manual backup.
     */
    public function create(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500',
        ]);

        $backup = $this->backupService->createManualBackup(
            $router,
            $validated['name'],
            $validated['reason'] ?? null,
            Auth::id()
        );

        if ($backup) {
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully.',
                'backup' => [
                    'id' => $backup->id,
                    'name' => $backup->notes,
                    'created_at' => $backup->created_at->format('Y-m-d H:i:s'),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to create backup.',
        ], 500);
    }

    /**
     * List backups for a router.
     */
    public function list(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $type = $request->query('type');
        $backups = $this->backupService->listBackups($router, $type);

        return response()->json([
            'success' => true,
            'backups' => $backups->map(function ($backup) {
                return [
                    'id' => $backup->id,
                    'type' => $backup->backup_type,
                    'notes' => $backup->notes,
                    'created_at' => $backup->created_at->format('Y-m-d H:i:s'),
                    'created_by' => $backup->created_by,
                ];
            }),
        ]);
    }

    /**
     * Restore router from backup.
     */
    public function restore(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $validated = $request->validate([
            'backup_id' => 'required|exists:router_configuration_backups,id',
        ]);

        $backup = RouterConfigurationBackup::findOrFail($validated['backup_id']);

        // Verify backup belongs to this router
        if ($backup->router_id !== $router->id) {
            return response()->json([
                'success' => false,
                'message' => 'Backup does not belong to this router.',
            ], 400);
        }

        $success = $this->backupService->restoreFromBackup($router, $backup);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Configuration restored successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to restore configuration.',
        ], 500);
    }

    /**
     * Delete a backup.
     */
    public function destroy(int $routerId, int $backupId): JsonResponse
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $backup = RouterConfigurationBackup::where('router_id', $router->id)
            ->findOrFail($backupId);

        // Log backup deletion for audit trail
        Log::info('Router backup deleted', [
            'router_id' => $router->id,
            'router_name' => $router->name,
            'backup_id' => $backup->id,
            'backup_type' => $backup->backup_type,
            'backup_notes' => $backup->notes,
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);

        $backup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Backup deleted successfully.',
        ]);
    }

    /**
     * Clean up old backups.
     */
    public function cleanup(Request $request, int $routerId): JsonResponse
    {
        $router = MikrotikRouter::where('tenant_id', getCurrentTenantId())
            ->findOrFail($routerId);

        $validated = $request->validate([
            'retention_days' => 'nullable|integer|min:1|max:365',
        ]);

        $retentionDays = $validated['retention_days'] ?? 30;
        $deletedCount = $this->backupService->cleanupOldBackups($router, $retentionDays);

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} old backup(s).",
            'deleted_count' => $deletedCount,
        ]);
    }
}
