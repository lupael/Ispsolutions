<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RadiusServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNetworkUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * NetworkUserController - kept for backward API compatibility.
 * 
 * Note: NetworkUser model has been eliminated. This controller now works with
 * User model (operator_level = 100 for customers) but maintains the same API contract.
 */
class NetworkUserController extends Controller
{
    public function __construct(
        private readonly RadiusServiceInterface $radiusService
    ) {}

    /**
     * List all customers (network users).
     * Note: Now uses User model with operator_level = 100.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::select([
            'id', 'name', 'username', 'email', 'service_type',
            'package_id', 'status', 'tenant_id',
            'created_at', 'updated_at',
        ])->where('operator_level', 100)
          ->with(['package:id,name,price,bandwidth_upload,bandwidth_download']);

        if ($request->has('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Create a new customer (network user).
     * Note: Now uses User model with operator_level = 100.
     */
    public function store(StoreNetworkUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $password = $data['radius_password'] ?? $data['password'] ?? null;
        unset($data['password']); // Don't store plain password

        $data['operator_level'] = 100;
        $user = User::create($data);

        // Sync to RADIUS if password provided
        if ($password) {
            $this->radiusService->syncUser($user, $password);
        }

        return response()->json([
            'message' => 'Customer created successfully',
            'data' => $user->load('package'),
        ], 201);
    }

    /**
     * Get a specific customer (network user).
     * Note: Now uses User model with operator_level = 100.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::select([
            'id', 'name', 'username', 'email', 'service_type',
            'package_id', 'status', 'tenant_id',
            'created_at', 'updated_at',
        ])->where('operator_level', 100)
          ->with([
            'package:id,name,price,bandwidth_upload,bandwidth_download',
        ])->findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update a customer (network user).
     * Note: Now uses User model with operator_level = 100.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::where('operator_level', 100)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) use ($id) {
                    $exists = User::where('email', $value)
                        ->where('operator_level', 100)
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($exists) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            'service_type' => 'sometimes|in:pppoe,hotspot,static_ip',
            'package_id' => 'nullable|exists:packages,id',
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
            'message' => 'Customer updated successfully',
            'data' => $user->load('package'),
        ]);
    }

    /**
     * Delete a customer (network user).
     * Note: Now uses User model with operator_level = 100.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::where('operator_level', 100)->findOrFail($id);

        // Delete from RADIUS
        $this->radiusService->deleteUser($user->username);

        $user->delete();

        return response()->json([
            'message' => 'Customer deleted successfully',
        ]);
    }

    /**
     * Sync customer to RADIUS.
     * Note: Now uses User model with operator_level = 100.
     */
    public function syncToRadius(Request $request, int $id): JsonResponse
    {
        $user = User::where('operator_level', 100)->findOrFail($id);

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
                'message' => 'Failed to sync customer to RADIUS',
            ], 400);
        }

        return response()->json([
            'message' => 'Customer synced to RADIUS successfully',
        ]);
    }
}
