<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\User;
use App\Services\MikrotikApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Customer Backup Controller
 *
 * Mirrors customer data to router PPP secrets for fallback authentication.
 * Only runs when primary_authenticator !== 'Radius'.
 */
class CustomerBackupController extends Controller
{
    public function __construct(
        private readonly MikrotikApiService $apiService
    ) {}

    /**
     * Backup/mirror a single customer to router PPP secret.
     */
    public function backupCustomer(Request $request, int $customerId): JsonResponse
    {
        try {
            $customer = User::with('package')
                ->where('tenant_id', getCurrentTenantId())
                ->findOrFail($customerId);

            $routerId = $request->input('router_id');

            if (! $routerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router ID is required',
                ], 400);
            }

            $router = MikrotikRouter::findOrFail($routerId);

            // Only backup if not using RADIUS as primary authenticator
            if (strtolower($router->primary_auth ?? '') === 'radius') {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is configured for RADIUS authentication. Customer backup is not needed.',
                ], 400);
            }

            $pppSecret = $this->buildPppSecret($customer);

            // Check if secret already exists
            $menu = 'ppp/secret';
            $existRows = $this->apiService->getMktRows($router, $menu, ['name' => $pppSecret['name']]);

            if (! empty($existRows)) {
                // Update existing secret
                $existRow = array_shift($existRows);
                $success = $this->apiService->editMktRow($router, $menu, $existRow, $pppSecret);

                $message = $success
                    ? 'Customer PPP secret updated successfully'
                    : 'Failed to update customer PPP secret';

                return response()->json([
                    'success' => $success,
                    'message' => $message,
                    'action' => 'update',
                ]);
            } else {
                // Add new secret
                $result = $this->apiService->addMktRows($router, $menu, [$pppSecret]);

                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success']
                        ? 'Customer PPP secret created successfully'
                        : 'Failed to create customer PPP secret',
                    'action' => 'create',
                    'errors' => $result['errors'] ?? [],
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error backing up customer to router', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Backup all customers to router PPP secrets.
     */
    public function backupAllCustomers(Request $request, int $routerId): JsonResponse
    {
        try {
            $router = MikrotikRouter::findOrFail($routerId);

            // Only backup if not using RADIUS as primary authenticator
            if (strtolower($router->primary_auth ?? '') === 'radius') {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is configured for RADIUS authentication. Bulk backup is not needed.',
                ], 400);
            }

            $customers = User::with('package')
                ->where('tenant_id', getCurrentTenantId())
                ->where('status', 'active')
                ->get();

            $menu = 'ppp/secret';
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($customers as $customer) {
                try {
                    $pppSecret = $this->buildPppSecret($customer);

                    // Check if secret already exists
                    $existRows = $this->apiService->getMktRows($router, $menu, ['name' => $pppSecret['name']]);

                    if (! empty($existRows)) {
                        // Update existing
                        $existRow = array_shift($existRows);
                        $success = $this->apiService->editMktRow($router, $menu, $existRow, $pppSecret);

                        if ($success) {
                            $successCount++;
                        } else {
                            $failCount++;
                        }
                    } else {
                        // Add new
                        $result = $this->apiService->addMktRows($router, $menu, [$pppSecret]);

                        if ($result['success']) {
                            $successCount++;
                        } else {
                            $failCount++;
                            if (! empty($result['errors'])) {
                                $errors[] = [
                                    'customer' => $customer->username,
                                    'error' => $result['errors'][0]['error'] ?? 'Unknown error',
                                ];
                            }
                        }
                    }

                } catch (\Exception $e) {
                    $failCount++;
                    $errors[] = [
                        'customer' => $customer->username ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Error backing up customer', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Bulk customer backup completed', [
                'router_id' => $routerId,
                'total' => $customers->count(),
                'success' => $successCount,
                'failed' => $failCount,
            ]);

            return response()->json([
                'success' => $failCount === 0,
                'message' => "Backup completed: {$successCount} successful, {$failCount} failed",
                'total' => $customers->count(),
                'succeeded' => $successCount,
                'failed' => $failCount,
                'errors' => array_slice($errors, 0, 10), // Limit to first 10 errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk customer backup', [
                'router_id' => $routerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove customer PPP secret from router.
     */
    public function removeCustomer(Request $request, int $customerId): JsonResponse
    {
        try {
            $customer = User::where('tenant_id', getCurrentTenantId())
                ->findOrFail($customerId);

            $routerId = $request->input('router_id');

            if (! $routerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router ID is required',
                ], 400);
            }

            $router = MikrotikRouter::findOrFail($routerId);

            $menu = 'ppp/secret';
            $existRows = $this->apiService->getMktRows($router, $menu, ['name' => $customer->username]);

            if (empty($existRows)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer PPP secret not found on router',
                ]);
            }

            $success = $this->apiService->removeMktRows($router, $menu, $existRows);

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? 'Customer PPP secret removed successfully'
                    : 'Failed to remove customer PPP secret',
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing customer from router', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build PPP secret array from customer data.
     */
    private function buildPppSecret(User $customer): array
    {
        $package = $customer->package;

        return [
            'name' => $customer->username,
            'password' => $customer->radius_password, // Plain password for router
            'service' => 'pppoe',
            'profile' => $package?->mikrotik_profile ?? 'default',
            'local-address' => config('mikrotik.ppp_local_address', '10.0.0.1'),
            'remote-address' => $customer->static_ip ?? 'pool1',
            'comment' => "Customer ID: {$customer->id}",
            'disabled' => $customer->status !== 'active' ? 'yes' : 'no',
        ];
    }
}
