<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RadiusServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\RadAcct;
use App\Models\User;
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
        // Log incoming RADIUS authentication request
        Log::info('RADIUS authentication request received', [
            'username' => $request->input('username'),
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('RADIUS authentication validation failed', [
                'username' => $request->input('username'),
                'errors' => $validator->errors()->toArray(),
                'client_ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->radiusService->authenticate([
            'username' => $request->username,
            'password' => $request->password,
        ]);

        if (! $result['success']) {
            Log::warning('RADIUS authentication failed', [
                'username' => $request->username,
                'client_ip' => $request->ip(),
                'reason' => $result['message'] ?? 'Invalid credentials',
            ]);
            
            return response()->json([
                'message' => 'Authentication failed',
                'authenticated' => false,
            ], 401);
        }

        Log::info('RADIUS authentication successful', [
            'username' => $request->username,
            'client_ip' => $request->ip(),
        ]);

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
        // Log incoming RADIUS accounting start request
        Log::info('RADIUS accounting start request received', [
            'username' => $request->input('username'),
            'session_id' => $request->input('session_id'),
            'nas_ip_address' => $request->input('nas_ip_address'),
            'framed_ip_address' => $request->input('framed_ip_address'),
            'client_ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

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
            Log::warning('RADIUS accounting start validation failed', [
                'username' => $request->input('username'),
                'session_id' => $request->input('session_id'),
                'errors' => $validator->errors()->toArray(),
                'client_ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->radiusService->accountingStart($validator->validated());

        if (! $success) {
            Log::error('RADIUS accounting start failed', [
                'username' => $request->username,
                'session_id' => $request->session_id,
                'client_ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Failed to start accounting session',
            ], 400);
        }

        Log::info('RADIUS accounting start successful', [
            'username' => $request->username,
            'session_id' => $request->session_id,
            'client_ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Accounting session started successfully',
        ], 201);
    }

    /**
     * Update accounting session
     */
    public function accountingUpdate(Request $request): JsonResponse
    {
        // Log incoming RADIUS accounting update request
        Log::debug('RADIUS accounting update request received', [
            'username' => $request->input('username'),
            'session_id' => $request->input('session_id'),
            'session_time' => $request->input('session_time'),
            'input_octets' => $request->input('input_octets'),
            'output_octets' => $request->input('output_octets'),
            'client_ip' => $request->ip(),
        ]);

        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'username' => 'required|string',
            'session_time' => 'required|integer|min:0',
            'input_octets' => 'required|integer|min:0',
            'output_octets' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            Log::warning('RADIUS accounting update validation failed', [
                'username' => $request->input('username'),
                'session_id' => $request->input('session_id'),
                'errors' => $validator->errors()->toArray(),
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->radiusService->accountingUpdate($validator->validated());

        if (! $success) {
            Log::error('RADIUS accounting update failed', [
                'username' => $request->username,
                'session_id' => $request->session_id,
            ]);
            
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
        // Log incoming RADIUS accounting stop request
        Log::info('RADIUS accounting stop request received', [
            'username' => $request->input('username'),
            'session_id' => $request->input('session_id'),
            'session_time' => $request->input('session_time'),
            'input_octets' => $request->input('input_octets'),
            'output_octets' => $request->input('output_octets'),
            'terminate_cause' => $request->input('terminate_cause'),
            'client_ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'username' => 'required|string',
            'session_time' => 'required|integer|min:0',
            'input_octets' => 'required|integer|min:0',
            'output_octets' => 'required|integer|min:0',
            'terminate_cause' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('RADIUS accounting stop validation failed', [
                'username' => $request->input('username'),
                'session_id' => $request->input('session_id'),
                'errors' => $validator->errors()->toArray(),
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $this->radiusService->accountingStop($validator->validated());

        if (! $success) {
            Log::error('RADIUS accounting stop failed', [
                'username' => $request->username,
                'session_id' => $request->session_id,
            ]);
            
            return response()->json([
                'message' => 'Failed to stop accounting session',
            ], 400);
        }

        Log::info('RADIUS accounting stop successful', [
            'username' => $request->username,
            'session_id' => $request->session_id,
        ]);

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
     * Sync a customer to RADIUS (now uses User model with is_subscriber = true).
     * Note: Migrated from NetworkUser to User model, kept for backward compatibility.
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

        $user = User::where('username', $username)
            ->where('is_subscriber', true)
            ->firstOrFail();

        $password = $request->password ?? null;
        $attributes = $password ? ['password' => $password] : [];

        $success = $this->radiusService->syncUser($user, $attributes);

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

    /**
     * Get real-time bandwidth stats for a customer (by customer ID).
     * Note: Migrated from NetworkUser to User model with is_subscriber = true.
     */
    public function getRealTimeStats(int $customerId): JsonResponse
    {
        try {
            // Ensure user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Get the customer by ID (User model with is_subscriber = true)
            $user = User::where('is_subscriber', true)->findOrFail($customerId);
            
            // Check tenant isolation
            if ($user->tenant_id !== auth()->user()->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to customer data',
                ], 403);
            }

            // Get active session data from radacct
            $activeSession = RadAcct::where('username', $user->username)
                ->whereNull('acctstoptime')
                ->orderByDesc('acctstarttime')
                ->first();

            if (!$activeSession) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'offline',
                        'upload' => 0,
                        'download' => 0,
                        'session_time' => 0,
                        'ip_address' => null,
                        'nas_identifier' => null,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'online',
                    'upload' => (int) $activeSession->acctinputoctets,
                    'download' => (int) $activeSession->acctoutputoctets,
                    'session_time' => (int) $activeSession->acctsessiontime,
                    'ip_address' => $activeSession->framedipaddress,
                    'nas_identifier' => $activeSession->nasipaddress,
                    'session_id' => $activeSession->acctsessionid,
                    'started_at' => $activeSession->acctstarttime,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to get real-time bandwidth stats', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching bandwidth data',
            ], 500);
        }
    }
}
