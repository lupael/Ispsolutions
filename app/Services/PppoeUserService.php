<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PppoeUserServiceInterface;
use App\Helpers\RouterCommentHelper;
use App\Models\User;
use App\Models\MikrotikRouter;
use App\Models\MikrotikProfile;
use Illuminate\Support\Facades\Log;

class PppoeUserService implements PppoeUserServiceInterface
{
    public function __construct(private readonly MikrotikApiService $mikrotikApiService)
    {
    }

    public function provisionPppoeUser(User $customer, MikrotikRouter $router, ?MikrotikProfile $profile = null, ?string $staticIp = null): bool
    {
        if (!$this->mikrotikApiService->connect()) {
            Log::error('Cannot connect to router for PPP secret provisioning', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
            ]);
            return false;
        }

        $api = $this->mikrotikApiService->getApi();

        try {
            // Step 1: Ensure PPP profile exists
            if ($profile) {
                $this->ensurePppProfileExists($api, $router, $profile);
            }

            // Step 2: Check if secret already exists
            $existingRows = $api->comm('/ppp/secret/print', [
                '?name' => $customer->username,
            ]);

            // Step 3: Prepare PPP secret data
            $pppSecret = [
                'name' => $customer->username,
                'password' => $customer->radius_password ?? $customer->username,
                'disabled' => $customer->status === 'active' ? 'no' : 'yes',
                'service' => 'pppoe',
            ];

            if ($profile) {
                $pppSecret['profile'] = $profile->name;
            }

            if ($staticIp) {
                $pppSecret['remote-address'] = $staticIp;
            }

            if (!empty($customer->mac_address)) {
                $pppSecret['caller-id'] = $customer->mac_address;
            }

            if (class_exists(RouterCommentHelper::class)) {
                $pppSecret['comment'] = RouterCommentHelper::buildComment($customer);
            } else {
                $pppSecret['comment'] = "customer_id:{$customer->id},status:{$customer->status}";
            }

            // Step 4: Create or update secret
            if (!empty($existingRows)) {
                $pppSecret['.id'] = $existingRows[0]['.id'];
                $api->comm('/ppp/secret/set', $pppSecret);
                Log::info('PPP secret updated on router', [
                    'router_id' => $router->id,
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                ]);
            } else {
                $api->comm('/ppp/secret/add', $pppSecret);
                Log::info('PPP secret created on router', [
                    'router_id' => $router->id,
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to provision PPP secret', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        } finally {
            $this->mikrotikApiService->disconnect();
        }
    }

    public function deprovisionPppoeUser(User $customer, MikrotikRouter $router, bool $delete = false): bool
    {
        if (!$this->mikrotikApiService->connect()) {
            Log::error('Cannot connect to router for PPP secret deprovisioning', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
            ]);
            return false;
        }

        $api = $this->mikrotikApiService->getApi();

        try {
            $existingRows = $api->comm('/ppp/secret/print', [
                '?name' => $customer->username,
            ]);

            if (empty($existingRows)) {
                Log::warning('PPP secret not found on router', [
                    'router_id' => $router->id,
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                ]);
                return true;
            }

            $secretId = $existingRows[0]['.id'];

            if ($delete) {
                $api->comm('/ppp/secret/remove', [
                    '.id' => $secretId,
                ]);
                Log::info('PPP secret deleted from router', [
                    'router_id' => $router->id,
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                ]);
            } else {
                $api->comm('/ppp/secret/set', [
                    '.id' => $secretId,
                    'disabled' => 'yes',
                ]);
                Log::info('PPP secret disabled on router', [
                    'router_id' => $router->id,
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to deprovision PPP secret', [
                'router_id' => $router->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        } finally {
            $this->mikrotikApiService->disconnect();
        }
    }

    public function bulkProvisionPppoeUsers(array $customers, MikrotikRouter $router): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($customers as $customer) {
            $profile = null; // You may need to fetch the profile for each customer
            $staticIp = null; // You may need to fetch the static IP for each customer

            if ($this->provisionPppoeUser($customer, $router, $profile, $staticIp)) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = $customer->username;
            }
        }

        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }

    protected function ensurePppProfileExists($api, MikrotikRouter $router, MikrotikProfile $profile): void
    {
        $existingProfiles = $api->comm('/ppp/profile/print', [
            '?name' => $profile->name,
        ]);

        if (empty($existingProfiles)) {
            $profileData = [
                'name' => $profile->name,
                'on-up' => ':local sessions [/ppp active print count-only where name=$user]; :if ( $sessions > 1) do={ :log info ("disconnecting " . $user . " duplicate" ); /ppp active remove [find where (name=$user && uptime<00:00:30 )]; }',
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

            $api->comm('/ppp/profile/add', $profileData);

            Log::info('PPP profile created on router', [
                'router_id' => $router->id,
                'profile_name' => $profile->name,
            ]);
        }
    }
}
