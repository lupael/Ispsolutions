<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\MikrotikServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\MikrotikPppoeUser;
use App\Models\MikrotikRouter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MikrotikController extends Controller
{
    public function __construct(
        private readonly MikrotikServiceInterface $mikrotikService
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

        if ($sessions === null) {
            return response()->json([
                'message' => 'Failed to retrieve sessions from router',
            ], 400);
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

        if ($profiles === null) {
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
}
