<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\RouterCommentHelper;
use App\Models\Customer;
use App\Models\MikrotikProfile;
use App\Models\MikrotikRouter;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * PppSecretProvisioningService - Provision PPP secrets to MikroTik routers
 * 
 * Following IspBills pattern for PPP secret management
 */
class PppSecretProvisioningService
{
    /**
     * Provision (create or update) a PPP secret on router
     * 
     * @param User $customer Customer/user to provision
     * @param MikrotikRouter $router Router to provision to
     * @param MikrotikProfile|null $profile PPP profile to use
     * @param string|null $staticIp Static IP address (optional)
     * @return bool Success status
     */
    public function provisionPppSecret(
        User $customer, 
        MikrotikRouter $router, 
        ?MikrotikProfile $profile = null,
        ?string $staticIp = null
    ): bool {
        // Connect to router
        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            Log::error('Cannot connect to router for PPP secret provisioning', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
            ]);
            return false;
        }

        try {
            // Step 1: Ensure PPP profile exists (IspBills pattern)
            if ($profile) {
                $this->ensurePppProfileExists($api, $router, $profile);
            }
            
            // Step 2: Check if secret already exists
            $existingRows = $api->getMktRows('ppp_secret', ['name' => $customer->username]);
            
            // Step 3: Prepare PPP secret data
            $pppSecret = [
                'name' => $customer->username,
                'password' => $customer->password,
                'disabled' => $customer->status === 'active' ? 'no' : 'yes',
            ];
            
            // Add profile if provided
            if ($profile) {
                $pppSecret['profile'] = $profile->name;
            }
            
            // Add static IP if provided (IspBills pattern for static allocation)
            if ($staticIp) {
                $pppSecret['remote-address'] = $staticIp;
            } elseif ($profile && $profile->ip_allocation_mode === 'static') {
                // If profile requires static IP but none provided, use customer's login_ip
                if (!empty($customer->login_ip)) {
                    $pppSecret['remote-address'] = $customer->login_ip;
                }
            }
            
            // Add customer metadata comment (IspBills pattern)
            if (class_exists(RouterCommentHelper::class)) {
                $pppSecret['comment'] = RouterCommentHelper::buildComment($customer);
            } else {
                // Fallback comment format
                $pppSecret['comment'] = "customer_id:{$customer->id},status:{$customer->status}";
            }
            
            // Step 4: Create or update secret
            if (!empty($existingRows)) {
                // Update existing secret
                $existingRow = $existingRows[0];
                $success = $api->editMktRow('ppp_secret', $existingRow, $pppSecret);
                
                if ($success) {
                    Log::info('PPP secret updated on router', [
                        'router_id' => $router->id,
                        'customer_id' => $customer->id,
                        'username' => $customer->username,
                    ]);
                }
            } else {
                // Create new secret
                $success = $api->addMktRows('ppp_secret', [$pppSecret]);
                
                if ($success) {
                    Log::info('PPP secret created on router', [
                        'router_id' => $router->id,
                        'customer_id' => $customer->id,
                        'username' => $customer->username,
                    ]);
                }
            }
            
            $api->disconnect();
            return $success;
        } catch (\Exception $e) {
            $api->disconnect();
            
            Log::error('Failed to provision PPP secret', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Deprovision (delete or disable) a PPP secret from router
     * 
     * @param User $customer Customer to deprovision
     * @param MikrotikRouter $router Router to deprovision from
     * @param bool $delete True to delete, false to disable
     * @return bool Success status
     */
    public function deprovisionPppSecret(
        User $customer,
        MikrotikRouter $router,
        bool $delete = false
    ): bool {
        $api = new RouterosAPI([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
            'debug' => config('app.debug'),
        ]);

        if (!$api->connect()) {
            Log::error('Cannot connect to router for PPP secret deprovisioning', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
            ]);
            return false;
        }

        try {
            // Find the secret
            $existingRows = $api->getMktRows('ppp_secret', ['name' => $customer->username]);
            
            if (empty($existingRows)) {
                Log::warning('PPP secret not found on router', [
                    'router_id' => $router->id,
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                ]);
                $api->disconnect();
                return true; // Not an error if it doesn't exist
            }
            
            $existingRow = $existingRows[0];
            
            if ($delete) {
                // Delete the secret
                $success = $api->removeMktRows('ppp_secret', [$existingRow]);
                
                if ($success) {
                    Log::info('PPP secret deleted from router', [
                        'router_id' => $router->id,
                        'customer_id' => $customer->id,
                        'username' => $customer->username,
                    ]);
                }
            } else {
                // Disable the secret
                $success = $api->editMktRow('ppp_secret', $existingRow, ['disabled' => 'yes']);
                
                if ($success) {
                    Log::info('PPP secret disabled on router', [
                        'router_id' => $router->id,
                        'customer_id' => $customer->id,
                        'username' => $customer->username,
                    ]);
                }
            }
            
            $api->disconnect();
            return $success;
        } catch (\Exception $e) {
            $api->disconnect();
            
            Log::error('Failed to deprovision PPP secret', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Ensure PPP profile exists on router before creating secrets (IspBills pattern)
     * 
     * @param RouterosAPI $api Connected API instance
     * @param MikrotikRouter $router Router instance
     * @param MikrotikProfile $profile Profile to ensure exists
     * @return void
     */
    protected function ensurePppProfileExists(
        RouterosAPI $api,
        MikrotikRouter $router,
        MikrotikProfile $profile
    ): void {
        // Check if profile exists
        $existingProfiles = $api->getMktRows('ppp_profile', ['name' => $profile->name]);
        
        if (!empty($existingProfiles)) {
            return; // Profile already exists
        }
        
        // Create profile on router
        $profileData = [
            'name' => $profile->name,
        ];
        
        if ($profile->local_address) {
            $profileData['local-address'] = $profile->local_address;
        }
        
        if ($profile->remote_address) {
            $profileData['remote-address'] = $profile->remote_address;
        }
        
        if ($profile->rate_limit) {
            $profileData['rate-limit'] = $profile->rate_limit;
        }
        
        if ($profile->session_timeout) {
            $profileData['session-timeout'] = $profile->session_timeout;
        }
        
        $api->addMktRows('ppp_profile', [$profileData]);
        
        Log::info('PPP profile created on router', [
            'router_id' => $router->id,
            'profile_name' => $profile->name,
        ]);
    }

    /**
     * Bulk provision multiple customers to router
     * 
     * @param array $customers Array of User models
     * @param MikrotikRouter $router Router to provision to
     * @return array Result with success count and errors
     */
    public function bulkProvisionPppSecrets(array $customers, MikrotikRouter $router): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        
        foreach ($customers as $customer) {
            try {
                $success = $this->provisionPppSecret($customer, $router);
                
                if ($success) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = [
                        'customer_id' => $customer->id,
                        'username' => $customer->username,
                        'error' => 'Provisioning failed',
                    ];
                }
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'customer_id' => $customer->id,
                    'username' => $customer->username ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return [
            'success' => $failedCount === 0,
            'total' => count($customers),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }
}
