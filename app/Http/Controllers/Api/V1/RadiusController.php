<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RadiusServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\NetworkUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RadiusController extends Controller
{
    public function __construct(
        private readonly RadiusServiceInterface $radiusService
    ) {}

    /**
     * Authenticate a RADIUS user
     */
    public function authenticate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->radiusService->authenticate(
            $request->username,
            $request->password
        );

        if ($result === false) {
            return response()->json([
                'message' => 'Authentication failed',
                'authenticated' => false,
            ], 401);
        }

        return response()->json([
            'message' => 'Authentication successful',
            'authenticated' => true,
            'attributes' => $result,
        ]);
    }

    /**
     * Start accounting session
     */
    public function accountingStart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'session_id' => 'required|string',
            'nas_ip_address' => 'required|ip',
            'nas_port_id' => 'nullable|string',
            'framed_ip_address' => 'nullable|ip',
            'calling_station_id' => 'nullable|string',
            'called_station_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->radiusService->accountingStart($validator->validated());

        if (! $success) {
            return response()->json([
                'message' => 'Failed to start accounting session',
            ], 400);
        }

        return response()->json([
            'message' => 'Accounting session started successfully',
        ], 201);
    }

    /**
     * Update accounting session
     */
    public function accountingUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'username' => 'required|string',
            'session_time' => 'required|integer|min:0',
            'input_octets' => 'required|integer|min:0',
            'output_octets' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->radiusService->accountingUpdate($validator->validated());

        if (! $success) {
            return response()->json([
                'message' => 'Failed to update accounting session',
            ], 400);
        }

        return response()->json([
            'message' => 'Accounting session updated successfully',
        ]);
    }

    /**
     * Stop accounting session
     */
    public function accountingStop(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'username' => 'required|string',
            'session_time' => 'required|integer|min:0',
            'input_octets' => 'required|integer|min:0',
            'output_octets' => 'required|integer|min:0',
            'terminate_cause' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->radiusService->accountingStop($validator->validated());

        if (! $success) {
            return response()->json([
                'message' => 'Failed to stop accounting session',
            ], 400);
        }

        return response()->json([
            'message' => 'Accounting session stopped successfully',
        ]);
    }

    /**
     * Create a RADIUS user
     */
    public function createUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:radcheck,username',
            'password' => 'required|string|min:6',
            'attributes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $success = $this->radiusService->createUser(
            $data['username'],
            $data['password'],
            $data['attributes'] ?? []
        );

        if (! $success) {
            return response()->json([
                'message' => 'Failed to create RADIUS user',
            ], 400);
        }

        return response()->json([
            'message' => 'RADIUS user created successfully',
        ], 201);
    }

    /**
     * Update a RADIUS user
     */
    public function updateUser(Request $request, string $username): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:6',
            'attributes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $updateData = [];

        if (isset($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        if (isset($data['attributes'])) {
            $updateData['attributes'] = $data['attributes'];
        }

        $success = $this->radiusService->updateUser($username, $updateData);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to update RADIUS user. User may not exist.',
            ], 400);
        }

        return response()->json([
            'message' => 'RADIUS user updated successfully',
        ]);
    }

    /**
     * Delete a RADIUS user
     */
    public function deleteUser(string $username): JsonResponse
    {
        $success = $this->radiusService->deleteUser($username);

        if (! $success) {
            return response()->json([
                'message' => 'Failed to delete RADIUS user. User may not exist.',
            ], 400);
        }

        return response()->json([
            'message' => 'RADIUS user deleted successfully',
        ]);
    }

    /**
     * Sync a network user to RADIUS
     */
    public function syncUser(Request $request, string $username): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = NetworkUser::where('username', $username)->firstOrFail();

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

    /**
     * Get user statistics
     */
    public function getUserStats(string $username): JsonResponse
    {
        $stats = $this->radiusService->getUserStats($username);

        if (! $stats) {
            return response()->json([
                'message' => 'User not found or has no statistics',
            ], 404);
        }

        return response()->json([
            'username' => $username,
            'stats' => $stats,
        ]);
    }
}
