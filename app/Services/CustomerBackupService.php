<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MikrotikRouter;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Customer Backup Service
 *
 * Implements customer "backup/mirror" to router for PPPoE/Hotspot users.
 * This service runs only when primary_authenticator !== 'Radius' to provide
 * local authentication fallback.
 *
 * As described in issue #180, when RADIUS is down, routers can use local secrets.
 */
class CustomerBackupService
{
    public function __construct(
        private MikrotikApiService $mikrotikApiService
    ) {}

    /**
     * Sync customer to router as PPP secret for fallback authentication.
     *
     * @param User $customer The customer/user to sync
     * @param MikrotikRouter $router The router to sync to
     * @param array $profile PPP profile configuration
     *
     * @return array{success: bool, message: string, action: string|null}
     */
    public function syncCustomerToRouter(User $customer, MikrotikRouter $router, array $profile = []): array
    {
        // Only sync if router is not using RADIUS as primary authenticator
        if ($router->primary_auth === 'radius') {
            return [
                'success' => true,
                'message' => 'Skipped: Router uses RADIUS as primary authenticator',
                'action' => 'skipped',
            ];
        }

        try {
            // Prepare PPP secret data
            $pppSecret = $this->preparePppSecret($customer, $profile);

            // Check if secret already exists
            $existingSecrets = $this->mikrotikApiService->getMktRows($router, '/ppp/secret', ['name' => $pppSecret['name']]);

            if (! empty($existingSecrets)) {
                // Update existing secret
                $existingSecret = $existingSecrets[0];
                $updated = $this->mikrotikApiService->editMktRow($router, '/ppp/secret', $existingSecret, $pppSecret);

                if ($updated) {
                    Log::info('Updated customer PPP secret on router', [
                        'customer_id' => $customer->id,
                        'router_id' => $router->id,
                        'username' => $pppSecret['name'],
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Customer updated on router',
                        'action' => 'updated',
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Failed to update customer on router',
                    'action' => 'update_failed',
                ];
            }

            // Add new secret
            $result = $this->mikrotikApiService->addMktRows($router, '/ppp/secret', [$pppSecret]);

            if ($result['success']) {
                Log::info('Added customer PPP secret to router', [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'username' => $pppSecret['name'],
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer added to router',
                    'action' => 'created',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to add customer to router',
                'action' => 'create_failed',
                'errors' => $result['errors'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Error syncing customer to router', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
                'action' => 'exception',
            ];
        }
    }

    /**
     * Sync all customers for a router.
     *
     * @return array{success: bool, message: string, total: int, synced: int, skipped: int, failed: int}
     */
    public function syncAllCustomersToRouter(MikrotikRouter $router): array
    {
        // Only sync if router is not using RADIUS as primary authenticator
        if ($router->primary_auth === 'radius') {
            return [
                'success' => true,
                'message' => 'Skipped: Router uses RADIUS as primary authenticator',
                'total' => 0,
                'synced' => 0,
                'skipped' => 0,
                'failed' => 0,
            ];
        }

        try {
            // Get all active customers for this tenant
            $customers = User::where('tenant_id', $router->tenant_id)
                ->where('role', 'customer')
                ->where('status', 'active')
                ->get();

            $total = $customers->count();
            $synced = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($customers as $customer) {
                $result = $this->syncCustomerToRouter($customer, $router);

                if ($result['success']) {
                    if ($result['action'] === 'skipped') {
                        $skipped++;
                    } else {
                        $synced++;
                    }
                } else {
                    $failed++;
                }
            }

            Log::info('Bulk customer sync to router completed', [
                'router_id' => $router->id,
                'total' => $total,
                'synced' => $synced,
                'skipped' => $skipped,
                'failed' => $failed,
            ]);

            return [
                'success' => true,
                'message' => "Synced {$synced} of {$total} customers",
                'total' => $total,
                'synced' => $synced,
                'skipped' => $skipped,
                'failed' => $failed,
            ];
        } catch (\Exception $e) {
            Log::error('Error during bulk customer sync', [
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Bulk sync failed: '.$e->getMessage(),
                'total' => 0,
                'synced' => 0,
                'skipped' => 0,
                'failed' => 0,
            ];
        }
    }

    /**
     * Remove customer from router.
     *
     * @return array{success: bool, message: string}
     */
    public function removeCustomerFromRouter(User $customer, MikrotikRouter $router): array
    {
        try {
            $username = $customer->username ?? $customer->email;

            // Get existing secrets
            $existingSecrets = $this->mikrotikApiService->getMktRows($router, '/ppp/secret', ['name' => $username]);

            if (empty($existingSecrets)) {
                return [
                    'success' => true,
                    'message' => 'Customer not found on router',
                ];
            }

            // Remove secrets
            $removed = $this->mikrotikApiService->removeMktRows($router, '/ppp/secret', $existingSecrets);

            if ($removed) {
                Log::info('Removed customer PPP secret from router', [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'username' => $username,
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer removed from router',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to remove customer from router',
            ];
        } catch (\Exception $e) {
            Log::error('Error removing customer from router', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Disable customer on router (for suspension).
     *
     * @return array{success: bool, message: string}
     */
    public function disableCustomerOnRouter(User $customer, MikrotikRouter $router): array
    {
        try {
            $username = $customer->username ?? $customer->email;

            // Get existing secrets
            $existingSecrets = $this->mikrotikApiService->getMktRows($router, '/ppp/secret', ['name' => $username]);

            if (empty($existingSecrets)) {
                return [
                    'success' => true,
                    'message' => 'Customer not found on router',
                ];
            }

            $existingSecret = $existingSecrets[0];

            // Disable the secret
            $disabled = $this->mikrotikApiService->editMktRow($router, '/ppp/secret', $existingSecret, [
                'disabled' => 'yes',
            ]);

            if ($disabled) {
                Log::info('Disabled customer PPP secret on router', [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'username' => $username,
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer disabled on router',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to disable customer on router',
            ];
        } catch (\Exception $e) {
            Log::error('Error disabling customer on router', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Enable customer on router.
     *
     * @return array{success: bool, message: string}
     */
    public function enableCustomerOnRouter(User $customer, MikrotikRouter $router): array
    {
        try {
            $username = $customer->username ?? $customer->email;

            // Get existing secrets
            $existingSecrets = $this->mikrotikApiService->getMktRows($router, '/ppp/secret', ['name' => $username]);

            if (empty($existingSecrets)) {
                // If secret doesn't exist, create it
                return $this->syncCustomerToRouter($customer, $router);
            }

            $existingSecret = $existingSecrets[0];

            // Enable the secret
            $enabled = $this->mikrotikApiService->editMktRow($router, '/ppp/secret', $existingSecret, [
                'disabled' => 'no',
            ]);

            if ($enabled) {
                Log::info('Enabled customer PPP secret on router', [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'username' => $username,
                ]);

                return [
                    'success' => true,
                    'message' => 'Customer enabled on router',
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to enable customer on router',
            ];
        } catch (\Exception $e) {
            Log::error('Error enabling customer on router', [
                'customer_id' => $customer->id,
                'router_id' => $router->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Prepare PPP secret data from customer information.
     */
    private function preparePppSecret(User $customer, array $profile = []): array
    {
        $username = $customer->username ?? $customer->email;

        // Get customer's active package/plan details
        $package = $customer->currentPackage;

        // Generate secure password if not available
        // SECURITY: Never use predictable passwords derived from usernames
        $password = $customer->password_plain;
        if (empty($password)) {
            // Generate a strong random password (16 characters)
            $password = bin2hex(random_bytes(8));
            Log::warning('Generated random password for PPP secret (password_plain not set)', [
                'customer_id' => $customer->id,
                'username' => $username,
            ]);
        }

        $pppSecret = [
            'name' => $username,
            'password' => $password,
            'service' => 'pppoe',
            'disabled' => $customer->status === 'active' ? 'no' : 'yes',
            'comment' => "Customer ID: {$customer->id}",
        ];

        // Add profile if provided
        if (! empty($profile['name'])) {
            $pppSecret['profile'] = $profile['name'];
        }

        // Add rate limit if package has speed limits
        if ($package) {
            $downloadSpeed = $package->download_speed ?? 0;
            $uploadSpeed = $package->upload_speed ?? 0;

            if ($downloadSpeed > 0 || $uploadSpeed > 0) {
                // Format: upload/download in bits per second
                $uploadBps = $uploadSpeed * 1024 * 1024; // Convert Mbps to bps
                $downloadBps = $downloadSpeed * 1024 * 1024;
                $pppSecret['rate-limit'] = "{$uploadBps}/{$downloadBps}";
            }
        }

        // Add local address (if router has an IP pool configured)
        if (! empty($profile['local_address'])) {
            $pppSecret['local-address'] = $profile['local_address'];
        }

        // Add remote address pool (if configured)
        if (! empty($profile['remote_address_pool'])) {
            $pppSecret['remote-address'] = $profile['remote_address_pool'];
        }

        return $pppSecret;
    }
}
