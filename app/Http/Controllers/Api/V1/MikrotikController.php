<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\MikrotikServiceInterface;
use App\Contracts\PackageSpeedServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\MikrotikIpPool;
use App\Models\MikrotikPppoeUser;
use App\Models\MikrotikQueue;
use App\Models\MikrotikRouter;
use App\Models\MikrotikVpnAccount;
use App\Models\PackageProfileMapping;
use App\Models\RouterConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MikrotikController extends Controller
{
    public function __construct(
        private readonly MikrotikServiceInterface $mikrotikService,
        private readonly PackageSpeedServiceInterface $packageSpeedService
    ) {}

    /**
     * List all routers
     */
    public function listRouters(Request $request): JsonResponse
    {
        $query = MikrotikRouter::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $routers = $query->paginate($request->get('per_page', 15));

        return response()->json($routers);
    }

    /**
     * Connect to a router
     */
    public function connectRouter(int $id): JsonResponse
    {
        $success = $this->mikrotikService->connectRouter($id);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to connect to router. Check credentials and connectivity.',
            ], 400);
        }

        return response()->json([
            'message' => 'Connected to router successfully',
        ]);
    }

    /**
     * Check router health
     */
    public function healthCheck(int $id): JsonResponse
    {
        $router = MikrotikRouter::findOrFail($id);

        $success = $this->mikrotikService->connectRouter($id);

        return response()->json([
            'router' => $router,
            'healthy' => $success,
            'checked_at' => now(),
        ]);
    }

    /**
     * List PPPoE users
     */
    public function listPppoeUsers(Request $request): JsonResponse
    {
        $query = MikrotikPppoeUser::with('router');

        if ($request->has('router_id')) {
            $query->where('router_id', $request->router_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Create a PPPoE user
     */
    public function createPppoeUser(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\MikrotikPppoeUser::class);

        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'service' => 'nullable|string',
            'profile' => 'nullable|string',
            'local_address' => 'nullable|ip',
            'remote_address' => 'nullable|ip',
            'status' => 'nullable|in:active,disabled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Create the user via MikroTik API
        $success = $this->mikrotikService->createPppoeUser($data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to create PPPoE user on router',
            ], 400);
        }

        // Store locally
        $user = MikrotikPppoeUser::create($data);

        return response()->json([
            'message' => 'PPPoE user created successfully',
            'data' => $user->load('router'),
        ], 201);
    }

    /**
     * Update a PPPoE user
     */
    public function updatePppoeUser(Request $request, string $username): JsonResponse
    {
        $this->authorize('update', \App\Models\MikrotikPppoeUser::class);

        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:6',
            'service' => 'nullable|string',
            'profile' => 'nullable|string',
            'local_address' => 'nullable|ip',
            'remote_address' => 'nullable|ip',
            'status' => 'nullable|in:active,disabled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = MikrotikPppoeUser::where('username', $username)->firstOrFail();

        $data = $validator->validated();

        // Update on MikroTik router
        $success = $this->mikrotikService->updatePppoeUser($username, $data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to update PPPoE user on router',
            ], 400);
        }

        // Update locally
        $user->update($data);

        return response()->json([
            'message' => 'PPPoE user updated successfully',
            'data' => $user,
        ]);
    }

    /**
     * Delete a PPPoE user
     */
    public function deletePppoeUser(string $username): JsonResponse
    {
        $this->authorize('delete', \App\Models\MikrotikPppoeUser::class);

        $user = MikrotikPppoeUser::where('username', $username)->firstOrFail();

        // Delete from MikroTik router
        $success = $this->mikrotikService->deletePppoeUser($username);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to delete PPPoE user from router',
            ], 400);
        }

        // Delete locally
        $user->delete();

        return response()->json([
            'message' => 'PPPoE user deleted successfully',
        ]);
    }

    /**
     * List active sessions
     */
    public function listActiveSessions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $sessions = $this->mikrotikService->getActiveSessions($request->router_id);

        if (empty($sessions)) {
            return response()->json([
                'message' => 'No active sessions found on router',
            ], 404);
        }

        return response()->json([
            'router_id' => $request->router_id,
            'sessions' => $sessions,
            'count' => count($sessions),
        ]);
    }

    /**
     * Disconnect a session
     */
    public function disconnectSession(string $id): JsonResponse
    {
        $success = $this->mikrotikService->disconnectSession($id);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to disconnect session. Session may not exist.',
            ], 400);
        }

        return response()->json([
            'message' => 'Session disconnected successfully',
        ]);
    }

    /**
     * List PPPoE profiles
     */
    public function listProfiles(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profiles = $this->mikrotikService->getProfiles($request->router_id);

        if (empty($profiles)) {
            return response()->json([
                'message' => 'Failed to retrieve profiles from router',
            ], 400);
        }

        return response()->json([
            'router_id' => $request->router_id,
            'profiles' => $profiles,
            'count' => count($profiles),
        ]);
    }

    /**
     * Create a PPPoE profile
     */
    public function createProfile(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\MikrotikRouter::class);

        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'name' => 'required|string|max:255',
            'local_address' => 'nullable|string',
            'remote_address' => 'nullable|string',
            'rate_limit' => 'nullable|string',
            'session_timeout' => 'nullable|integer',
            'idle_timeout' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->mikrotikService->createPppProfile($data['router_id'], $data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to create profile on router',
            ], 400);
        }

        return response()->json([
            'message' => 'Profile created successfully',
        ], 201);
    }

    /**
     * Import profiles from router
     */
    public function importProfiles(int $routerId): JsonResponse
    {
        $count = $this->mikrotikService->syncProfiles($routerId);

        if ($count === 0) {
            return response()->json([
                'message' => 'No profiles found or failed to import',
            ], 400);
        }

        return response()->json([
            'message' => 'Profiles imported successfully',
            'count' => $count,
        ]);
    }

    /**
     * List IP pools
     */
    public function listIpPools(Request $request): JsonResponse
    {
        $query = MikrotikIpPool::with('router');

        if ($request->has('router_id')) {
            $query->where('router_id', $request->router_id);
        }

        $pools = $query->paginate($request->get('per_page', 15));

        return response()->json($pools);
    }

    /**
     * Create an IP pool
     */
    public function createIpPool(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\IpPool::class);

        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'name' => 'required|string|max:255',
            'ranges' => 'required|array',
            'ranges.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->mikrotikService->createIpPool($data['router_id'], $data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to create IP pool on router',
            ], 400);
        }

        return response()->json([
            'message' => 'IP pool created successfully',
        ], 201);
    }

    /**
     * Import IP pools from router
     */
    public function importIpPools(int $routerId): JsonResponse
    {
        $count = $this->mikrotikService->syncIpPools($routerId);

        if ($count === 0) {
            return response()->json([
                'message' => 'No IP pools found or failed to import',
            ], 400);
        }

        return response()->json([
            'message' => 'IP pools imported successfully',
            'count' => $count,
        ]);
    }

    /**
     * Import secrets from router
     */
    public function importSecrets(int $routerId): JsonResponse
    {
        $count = $this->mikrotikService->syncSecrets($routerId);

        if ($count === 0) {
            return response()->json([
                'message' => 'No secrets found or failed to import',
            ], 400);
        }

        return response()->json([
            'message' => 'Secrets imported successfully',
            'count' => $count,
        ]);
    }

    /**
     * Configure router with one-click settings
     */
    public function configureRouter(Request $request, int $routerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ppp' => 'nullable|boolean',
            'pools' => 'nullable|boolean',
            'hotspot' => 'nullable|boolean',
            'pppoe' => 'nullable|boolean',
            'firewall' => 'nullable|boolean',
            'queue' => 'nullable|boolean',
            'radius' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = $validator->validated();

        if (empty(array_filter($config))) {
            return response()->json([
                'message' => 'No configuration options specified',
            ], 422);
        }

        $success = $this->mikrotikService->configureRouter($routerId, $config);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to configure router',
            ], 400);
        }

        return response()->json([
            'message' => 'Router configured successfully',
        ]);
    }

    /**
     * List VPN accounts
     */
    public function listVpnAccounts(Request $request): JsonResponse
    {
        $query = MikrotikVpnAccount::with('router');

        if ($request->has('router_id')) {
            $query->where('router_id', $request->router_id);
        }

        $accounts = $query->paginate($request->get('per_page', 15));

        return response()->json($accounts);
    }

    /**
     * Create a VPN account
     */
    public function createVpnAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'profile' => 'nullable|string',
            'enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->mikrotikService->createVpnAccount($data['router_id'], $data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to create VPN account on router',
            ], 400);
        }

        return response()->json([
            'message' => 'VPN account created successfully',
        ], 201);
    }

    /**
     * Get VPN status
     */
    public function getVpnStatus(int $routerId): JsonResponse
    {
        $status = $this->mikrotikService->getVpnStatus($routerId);

        return response()->json([
            'router_id' => $routerId,
            'status' => $status,
        ]);
    }

    /**
     * List queues
     */
    public function listQueues(Request $request): JsonResponse
    {
        if ($request->has('router_id')) {
            $queues = $this->mikrotikService->getQueues($request->router_id);

            return response()->json([
                'router_id' => $request->router_id,
                'queues' => $queues,
            ]);
        }

        $query = MikrotikQueue::with('router');
        $queues = $query->paginate($request->get('per_page', 15));

        return response()->json($queues);
    }

    /**
     * Create a queue
     */
    public function createQueue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'name' => 'required|string|max:255',
            'target' => 'required|string',
            'parent' => 'nullable|string',
            'max_limit' => 'nullable|string',
            'burst_limit' => 'nullable|string',
            'burst_threshold' => 'nullable|string',
            'burst_time' => 'nullable|integer',
            'priority' => 'nullable|integer|min:1|max:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->mikrotikService->createQueue($data['router_id'], $data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to create queue on router',
            ], 400);
        }

        return response()->json([
            'message' => 'Queue created successfully',
        ], 201);
    }

    /**
     * List firewall rules
     */
    public function listFirewallRules(int $routerId): JsonResponse
    {
        $rules = $this->mikrotikService->getFirewallRules($routerId);

        return response()->json([
            'router_id' => $routerId,
            'rules' => $rules,
        ]);
    }

    /**
     * Add firewall rule
     */
    public function addFirewallRule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'router_id' => 'required|exists:mikrotik_routers,id',
            'chain' => 'required|string',
            'action' => 'required|string',
            'protocol' => 'nullable|string',
            'src-address' => 'nullable|string',
            'dst-address' => 'nullable|string',
            'src-port' => 'nullable|string',
            'dst-port' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $routerId = $data['router_id'];
        unset($data['router_id']);

        $success = $this->mikrotikService->addFirewallRule($routerId, $data);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to add firewall rule on router',
            ], 400);
        }

        return response()->json([
            'message' => 'Firewall rule added successfully',
        ], 201);
    }

    /**
     * Map package to profile
     */
    public function mapPackageToProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'router_id' => 'required|exists:mikrotik_routers,id',
            'profile_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->packageSpeedService->mapPackageToProfile(
            $data['package_id'],
            $data['router_id'],
            $data['profile_name']
        );

        if (! $success) {
            return response()->json([
                'message' => 'Failed to map package to profile',
            ], 400);
        }

        return response()->json([
            'message' => 'Package mapped to profile successfully',
        ]);
    }

    /**
     * List package profile mappings
     */
    public function listPackageMappings(Request $request): JsonResponse
    {
        $query = PackageProfileMapping::with(['package', 'router']);

        if ($request->has('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->has('router_id')) {
            $query->where('router_id', $request->router_id);
        }

        $mappings = $query->paginate($request->get('per_page', 15));

        return response()->json($mappings);
    }

    /**
     * Apply speed to user
     */
    public function applySpeedToUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:network_users,id',
            'method' => 'nullable|string|in:router,radius',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->packageSpeedService->applySpeedToUser(
            $data['user_id'],
            $data['method'] ?? 'router'
        );

        if (! $success) {
            return response()->json([
                'message' => 'Failed to apply speed to user',
            ], 400);
        }

        return response()->json([
            'message' => 'Speed applied to user successfully',
        ]);
    }

    /**
     * List router configurations
     */
    public function listConfigurations(int $routerId): JsonResponse
    {
        $configurations = RouterConfiguration::where('router_id', $routerId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($configurations);
    }
}
