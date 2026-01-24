<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\RouterConfigurationTemplate;
use App\Services\RouterProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RouterProvisioningController extends Controller
{
    public function __construct(
        private RouterProvisioningService $provisioningService
    ) {}

    /**
     * Display router provisioning interface.
     */
    public function index(Request $request): View
    {
        $routers = MikrotikRouter::with(['configurations' => function ($query) {
            $query->latest()->limit(5);
        }])
            ->orderBy('name')
            ->get();

        $templates = RouterConfigurationTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedRouterId = $request->input('router_id');
        $selectedRouter = $selectedRouterId
            ? MikrotikRouter::find($selectedRouterId)
            : null;

        return view('panels.admin.routers.provision', compact(
            'routers',
            'templates',
            'selectedRouter'
        ));
    }

    /**
     * Show provisioning form for a specific router.
     */
    public function show(int $routerId): View
    {
        $router = MikrotikRouter::findOrFail($routerId);

        $templates = RouterConfigurationTemplate::where('is_active', true)
            ->orderBy('template_type')
            ->orderBy('name')
            ->get();

        $provisioningLogs = $this->provisioningService->getProvisioningLogs($routerId, 10);
        $backups = $this->provisioningService->getConfigurationBackups($routerId, 10);

        return view('panels.admin.routers.provision', compact(
            'router',
            'templates',
            'provisioningLogs',
            'backups'
        ));
    }

    /**
     * Preview configuration before applying.
     *
     * Security: Template access is restricted by tenant isolation (BelongsToTenant trait).
     * Configuration is generated server-side with validated template and sanitized variables.
     */
    public function preview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:router_configuration_templates,id',
            'variables' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = RouterConfigurationTemplate::findOrFail($request->template_id);
            $config = $template->interpolateConfiguration($request->variables);

            return response()->json([
                'success' => true,
                'configuration' => $config,
                'template_name' => $template->name,
                'template_type' => $template->template_type,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview configuration: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute provisioning.
     */
    public function provision(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'template_id' => 'required|exists:router_configuration_templates,id',
            'variables' => 'required|array',
            'variables.central_server_ip' => 'sometimes|ip',
            'variables.radius_secret' => 'sometimes|string',
            'variables.radius_server' => 'sometimes|ip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->provisioningService->provisionRouter(
                $request->router_id,
                $request->template_id,
                $request->variables,
                Auth::id()
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provisioning failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test router connectivity.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $connected = $this->provisioningService->verifyConnectivity($request->router_id);

            return response()->json([
                'success' => $connected,
                'message' => $connected
                    ? 'Router is reachable and responding'
                    : 'Cannot connect to router',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create configuration backup.
     */
    public function backup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $success = $this->provisioningService->backupConfiguration(
                $request->router_id,
                Auth::id(),
                'manual'
            );

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? 'Configuration backup created successfully'
                    : 'Failed to create backup',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rollback to a previous configuration.
     */
    public function rollback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'backup_id' => 'required|exists:router_configuration_backups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $success = $this->provisioningService->rollbackConfiguration(
                $request->router_id,
                $request->backup_id,
                Auth::id()
            );

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? 'Configuration rolled back successfully'
                    : 'Failed to rollback configuration',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get provisioning logs for a router.
     */
    public function logs(int $routerId): JsonResponse
    {
        try {
            $logs = $this->provisioningService->getProvisioningLogs($routerId, 20);

            return response()->json([
                'success' => true,
                'logs' => $logs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logs: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get configuration backups for a router.
     */
    public function backups(int $routerId): JsonResponse
    {
        try {
            $backups = $this->provisioningService->getConfigurationBackups($routerId, 20);

            return response()->json([
                'success' => true,
                'backups' => $backups,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve backups: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display template management interface.
     */
    public function templates(): View
    {
        $templates = RouterConfigurationTemplate::orderBy('template_type')
            ->orderBy('name')
            ->paginate(20);

        return view('panels.admin.routers.templates', compact('templates'));
    }

    /**
     * Show form to create a new template.
     */
    public function createTemplate(): View
    {
        return view('panels.admin.routers.template-form');
    }

    /**
     * Store a new template.
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'template_type' => 'required|in:radius,hotspot,pppoe,firewall,system,nat,walled_garden,suspended_pool,full_provisioning',
            'configuration' => 'required|array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = RouterConfigurationTemplate::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'template' => $template,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get template details.
     */
    public function getTemplate(int $templateId): JsonResponse
    {
        try {
            $template = RouterConfigurationTemplate::findOrFail($templateId);

            return response()->json([
                'success' => true,
                'template' => $template,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }
    }
}
