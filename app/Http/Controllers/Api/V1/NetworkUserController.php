<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RadiusServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNetworkUserRequest;
use App\Models\NetworkUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NetworkUserController extends Controller
{
    public function __construct(
        private readonly RadiusServiceInterface $radiusService
    ) {}

    /**
     * List all network users
     */
    public function index(Request $request): JsonResponse
    {
        // Optimized: Use eager loading with select to avoid N+1 queries
        $query = NetworkUser::select([
            'id', 'username', 'email', 'service_type', 
            'package_id', 'status', 'user_id', 'tenant_id', 
            'created_at', 'updated_at'
        ])->with(['package:id,name,price,bandwidth_upload,bandwidth_download']);

        if ($request->has('service_type')) {
            $query->byServiceType($request->service_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Create a new network user
     */
    public function store(StoreNetworkUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $password = $data['password'];
        unset($data['password']); // Don't store plain password in network_users

        $user = NetworkUser::create($data);

        // Sync to RADIUS
        $this->radiusService->syncUser($user, $password);

        return response()->json([
            'message' => 'Network user created successfully',
            'data' => $user->load('package'),
        ], 201);
    }

    /**
     * Get a specific network user
     */
    public function show(int $id): JsonResponse
    {
        // Optimized: Load only necessary relations with specific columns
        $user = NetworkUser::select([
            'id', 'username', 'email', 'service_type',
            'package_id', 'status', 'user_id', 'tenant_id',
            'created_at', 'updated_at'
        ])->with([
            'package:id,name,price,bandwidth_upload,bandwidth_download',
            'sessions' => function ($q) {
                $q->select('id', 'user_id', 'start_time', 'stop_time', 'input_octets', 'output_octets')
                  ->latest()
                  ->limit(10);
            }
        ])->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update a network user
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = NetworkUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:network_users,email,' . $id,
            'service_type' => 'sometimes|in:pppoe,hotspot,static_ip',
            'package_id' => 'nullable|exists:service_packages,id',
            'status' => 'nullable|in:active,suspended,expired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update($validator->validated());

        // If status changed, sync to RADIUS
        if ($request->has('status') || $request->has('package_id')) {
            $this->radiusService->syncUser($user, []);
        }

        return response()->json([
            'message' => 'Network user updated successfully',
            'data' => $user->load('package'),
        ]);
    }

    /**
     * Delete a network user
     */
    public function destroy(int $id): JsonResponse
    {
        $user = NetworkUser::findOrFail($id);

        // Delete from RADIUS
        $this->radiusService->deleteUser($user->username);

        $user->delete();

        return response()->json([
            'message' => 'Network user deleted successfully',
        ]);
    }

    /**
     * Sync user to RADIUS
     */
    public function syncToRadius(Request $request, int $id): JsonResponse
    {
        $user = NetworkUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $password = $request->password ?? null;

        $success = $this->radiusService->syncUser($user, $password);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to sync user to RADIUS',
            ], 400);
        }

        return response()->json([
            'message' => 'User synced to RADIUS successfully',
        ]);
    }
}
