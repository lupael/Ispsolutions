<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NetworkUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotspotScenarioDetectionService
{
    /**
     * Detect login scenario and return appropriate response.
     * 
     * Scenarios:
     * 1. Registered customer (normal login)
     * 2. New device/MAC change
     * 3. Multiple customers on same device
     * 4. Suspended account (volume limit)
     * 5. Suspended account (time limit)
     * 6. Unregistered mobile
     * 7. Device change for registered customer
     * 8. Link login (public access)
     * 9. Logout tracking
     * 10. Cross-radius server lookup
     */
    public function detectScenario(string $mobile, string $macAddress, ?int $tenantId = null): array
    {
        // Normalize MAC address
        $macAddress = strtoupper(str_replace([':', '-', '.'], '', $macAddress));

        // Scenario 1: Check if registered customer exists
        $customer = $this->findCustomerByMobile($mobile, $tenantId);

        if (!$customer) {
            // Scenario 6: Unregistered mobile
            return $this->handleUnregisteredMobile($mobile, $tenantId);
        }

        // Check if customer has MAC address in radcheck
        $radcheckEntry = $this->getRadcheckEntry($customer->username);

        if (!$radcheckEntry) {
            // First time login, no MAC set yet
            return $this->handleFirstTimeLogin($customer, $macAddress);
        }

        // Extract stored MAC from radcheck (Calling-Station-Id)
        $storedMac = $this->extractMacFromRadcheck($customer->username);

        // Scenario 3: Check if same MAC is used by multiple customers
        $customersOnSameMac = $this->findCustomersByMac($macAddress, $tenantId);
        if ($customersOnSameMac->count() > 1) {
            return $this->handleMultipleCustomersOnSameDevice($customersOnSameMac, $mobile, $macAddress);
        }

        // Scenario 2 & 7: Check if MAC has changed
        if ($storedMac && $storedMac !== $macAddress) {
            return $this->handleMacChange($customer, $storedMac, $macAddress);
        }

        // Check account status
        $accountStatus = $this->checkAccountStatus($customer);

        // Scenario 4: Volume limit exceeded
        if ($accountStatus['volume_limited']) {
            return $this->handleVolumeLimitExceeded($customer, $accountStatus['volume_usage']);
        }

        // Scenario 5: Time limit exceeded
        if ($accountStatus['time_limited']) {
            return $this->handleTimeLimitExceeded($customer, $accountStatus['time_usage']);
        }

        // Scenario 1: Normal login - all checks passed
        return $this->handleNormalLogin($customer, $macAddress);
    }

    /**
     * Scenario 1: Handle normal login.
     */
    private function handleNormalLogin(NetworkUser $customer, string $macAddress): array
    {
        return [
            'scenario' => 'normal_login',
            'allow_login' => true,
            'customer' => $customer,
            'message' => 'Login successful',
            'action' => 'allow',
        ];
    }

    /**
     * Scenario 2 & 7: Handle MAC address change.
     */
    private function handleMacChange(NetworkUser $customer, string $oldMac, string $newMac): array
    {
        return [
            'scenario' => 'mac_changed',
            'allow_login' => false,
            'customer' => $customer,
            'old_mac' => $oldMac,
            'new_mac' => $newMac,
            'message' => 'Device MAC address has changed. Do you want to update it?',
            'action' => 'confirm_mac_change',
            'options' => [
                'replace' => 'Replace old MAC with new MAC',
                'add_secondary' => 'Add as secondary device',
                'cancel' => 'Cancel login',
            ],
        ];
    }

    /**
     * Scenario 3: Handle multiple customers on same device.
     */
    private function handleMultipleCustomersOnSameDevice($customers, string $mobile, string $macAddress): array
    {
        return [
            'scenario' => 'multiple_customers_same_device',
            'allow_login' => false,
            'customers' => $customers,
            'mobile' => $mobile,
            'mac_address' => $macAddress,
            'message' => 'Multiple customers found using this device. Please select your account.',
            'action' => 'select_customer',
        ];
    }

    /**
     * Scenario 4: Handle volume limit exceeded.
     */
    private function handleVolumeLimitExceeded(NetworkUser $customer, array $volumeUsage): array
    {
        return [
            'scenario' => 'volume_limit_exceeded',
            'allow_login' => false,
            'customer' => $customer,
            'volume_usage' => $volumeUsage,
            'message' => 'Your data volume limit has been exceeded.',
            'action' => 'recharge',
            'details' => [
                'used' => $volumeUsage['used'],
                'limit' => $volumeUsage['limit'],
                'percentage' => $volumeUsage['percentage'],
            ],
        ];
    }

    /**
     * Scenario 5: Handle time limit exceeded.
     */
    private function handleTimeLimitExceeded(NetworkUser $customer, array $timeUsage): array
    {
        return [
            'scenario' => 'time_limit_exceeded',
            'allow_login' => false,
            'customer' => $customer,
            'time_usage' => $timeUsage,
            'message' => 'Your time limit has been exceeded.',
            'action' => 'recharge',
            'details' => [
                'used' => $timeUsage['used'],
                'limit' => $timeUsage['limit'],
                'remaining' => $timeUsage['remaining'],
            ],
        ];
    }

    /**
     * Scenario 6: Handle unregistered mobile.
     */
    private function handleUnregisteredMobile(string $mobile, ?int $tenantId): array
    {
        // Check if self-signup is enabled
        $selfSignupEnabled = $this->isSelfSignupEnabled($tenantId);

        return [
            'scenario' => 'unregistered_mobile',
            'allow_login' => false,
            'mobile' => $mobile,
            'message' => 'This mobile number is not registered.',
            'action' => $selfSignupEnabled ? 'self_signup' : 'contact_support',
            'self_signup_enabled' => $selfSignupEnabled,
        ];
    }

    /**
     * Handle first time login (no MAC set).
     */
    private function handleFirstTimeLogin(NetworkUser $customer, string $macAddress): array
    {
        // Auto-set MAC address for first time login
        $this->updateCustomerMac($customer->username, $macAddress);

        return [
            'scenario' => 'first_time_login',
            'allow_login' => true,
            'customer' => $customer,
            'mac_address' => $macAddress,
            'message' => 'Welcome! Your device has been registered.',
            'action' => 'allow',
        ];
    }

    /**
     * Find customer by mobile number.
     */
    private function findCustomerByMobile(string $mobile, ?int $tenantId): ?NetworkUser
    {
        $query = User::where('mobile', $mobile);
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $user = $query->first();
        
        if (!$user) {
            return null;
        }

        return NetworkUser::where('user_id', $user->id)->first();
    }

    /**
     * Find customers by MAC address.
     */
    private function findCustomersByMac(string $macAddress, ?int $tenantId)
    {
        // Query radcheck for Calling-Station-Id attribute
        $usernames = DB::table('radcheck')
            ->where('attribute', 'Calling-Station-Id')
            ->where('value', 'LIKE', "%{$macAddress}%")
            ->pluck('username');

        $query = NetworkUser::whereIn('username', $usernames);
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get();
    }

    /**
     * Get radcheck entry for customer.
     */
    private function getRadcheckEntry(string $username): ?object
    {
        return DB::table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Calling-Station-Id')
            ->first();
    }

    /**
     * Extract MAC from radcheck.
     */
    private function extractMacFromRadcheck(string $username): ?string
    {
        $entry = $this->getRadcheckEntry($username);
        if (!$entry) {
            return null;
        }

        // Extract and normalize MAC
        return strtoupper(str_replace([':', '-', '.'], '', $entry->value));
    }

    /**
     * Check account status (volume/time limits).
     */
    private function checkAccountStatus(NetworkUser $customer): array
    {
        $volumeLimited = false;
        $timeLimited = false;
        $volumeUsage = [];
        $timeUsage = [];

        // Check volume limits
        $volumeLimit = DB::table('customer_volume_limits')
            ->where('network_user_id', $customer->id)
            ->where('is_active', true)
            ->first();

        if ($volumeLimit) {
            $used = $volumeLimit->used_bytes ?? 0;
            $limit = $volumeLimit->limit_bytes ?? PHP_INT_MAX;
            $percentage = $limit > 0 ? ($used / $limit) * 100 : 0;

            if ($used >= $limit) {
                $volumeLimited = true;
            }

            $volumeUsage = [
                'used' => $used,
                'limit' => $limit,
                'percentage' => round($percentage, 2),
            ];
        }

        // Check time limits
        $timeLimit = DB::table('customer_time_limits')
            ->where('network_user_id', $customer->id)
            ->where('is_active', true)
            ->first();

        if ($timeLimit) {
            $used = $timeLimit->used_seconds ?? 0;
            $limit = $timeLimit->limit_seconds ?? PHP_INT_MAX;
            $remaining = max(0, $limit - $used);

            if ($used >= $limit) {
                $timeLimited = true;
            }

            $timeUsage = [
                'used' => $used,
                'limit' => $limit,
                'remaining' => $remaining,
            ];
        }

        return [
            'volume_limited' => $volumeLimited,
            'time_limited' => $timeLimited,
            'volume_usage' => $volumeUsage,
            'time_usage' => $timeUsage,
        ];
    }

    /**
     * Update customer MAC address.
     */
    private function updateCustomerMac(string $username, string $macAddress): void
    {
        DB::table('radcheck')->updateOrInsert(
            [
                'username' => $username,
                'attribute' => 'Calling-Station-Id',
            ],
            [
                'op' => ':=',
                'value' => $this->formatMacAddress($macAddress),
            ]
        );
    }

    /**
     * Format MAC address with colons.
     */
    private function formatMacAddress(string $mac): string
    {
        // Remove any existing separators
        $mac = strtoupper(str_replace([':', '-', '.'], '', $mac));
        
        // Add colons every 2 characters
        return implode(':', str_split($mac, 2));
    }

    /**
     * Check if self-signup is enabled for tenant.
     */
    private function isSelfSignupEnabled(?int $tenantId): bool
    {
        if (!$tenantId) {
            return false;
        }

        // Check tenant settings
        $setting = DB::table('tenant_settings')
            ->where('tenant_id', $tenantId)
            ->where('key', 'hotspot_self_signup_enabled')
            ->value('value');

        return $setting === '1' || $setting === 'true';
    }
}
