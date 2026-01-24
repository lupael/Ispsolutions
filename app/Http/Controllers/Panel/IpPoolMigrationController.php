<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\IpPool;
use App\Models\MikrotikProfile;
use App\Services\IpPoolMigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IpPoolMigrationController extends Controller
{
    protected IpPoolMigrationService $migrationService;

    public function __construct(IpPoolMigrationService $migrationService)
    {
        $this->migrationService = $migrationService;
    }

    /**
     * Display the IP pool migration form.
     */
    public function index(): View
    {
        $pools = IpPool::where('tenant_id', auth()->user()->tenant_id)->get();
        $profiles = MikrotikProfile::whereHas('router', function ($query) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        })->get();

        return view('panels.admin.ip-pools.migrate', compact('pools', 'profiles'));
    }

    /**
     * Validate the migration before starting.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'old_pool_id' => 'required|exists:ip_pools,id',
            'new_pool_id' => 'required|exists:ip_pools,id|different:old_pool_id',
            'profile_id' => 'required|exists:mikrotik_profiles,id',
        ]);

        $result = $this->migrationService->validateMigration(
            $request->old_pool_id,
            $request->new_pool_id,
            $request->profile_id
        );

        return response()->json($result);
    }

    /**
     * Start the IP pool migration.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'old_pool_id' => 'required|exists:ip_pools,id',
            'new_pool_id' => 'required|exists:ip_pools,id|different:old_pool_id',
            'profile_id' => 'required|exists:mikrotik_profiles,id',
        ]);

        $migrationId = $this->migrationService->startMigration(
            $request->old_pool_id,
            $request->new_pool_id,
            $request->profile_id
        );

        return response()->json([
            'success' => true,
            'migration_id' => $migrationId,
        ]);
    }

    /**
     * Get migration progress.
     */
    public function progress(string $migrationId): JsonResponse
    {
        $progress = $this->migrationService->getProgress($migrationId);
        $status = $this->migrationService->getStatus($migrationId);

        return response()->json([
            'progress' => $progress,
            'status' => $status,
        ]);
    }

    /**
     * Rollback a migration.
     */
    public function rollback(string $migrationId): JsonResponse
    {
        try {
            $count = $this->migrationService->rollback($migrationId);
            return response()->json([
                'success' => true,
                'message' => "Rolled back {$count} IP allocations",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
