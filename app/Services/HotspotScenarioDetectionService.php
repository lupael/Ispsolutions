<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HotspotLoginLog;
use App\Models\NetworkUser;
use App\Models\RadAcct;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    /**
     * Scenario 8: Generate temporary link token for public access.
     * 
     * @param int|null $tenantId Tenant ID for isolation
     * @param int $durationMinutes Link validity duration (default: 60 minutes)
     * @param array $metadata Additional metadata for the link
     * @return array Link login details
     */
    public function generateLinkLogin(?int $tenantId = null, int $durationMinutes = 60, array $metadata = []): array
    {
        // Generate unique link token
        $linkToken = Str::random(64);
        $expiresAt = now()->addMinutes($durationMinutes);
        $sessionId = Str::uuid()->toString();

        // Create login log entry
        $loginLog = HotspotLoginLog::create([
            'tenant_id' => $tenantId,
            'session_id' => $sessionId,
            'login_type' => HotspotLoginLog::TYPE_LINK,
            'scenario' => 'link_login',
            'link_token' => $linkToken,
            'link_expires_at' => $expiresAt,
            'is_link_login' => true,
            'status' => HotspotLoginLog::STATUS_ACTIVE,
            'metadata' => $metadata,
        ]);

        $loginUrl = route('hotspot.link-login', ['token' => $linkToken]);

        return [
            'scenario' => 'link_login',
            'link_token' => $linkToken,
            'session_id' => $sessionId,
            'expires_at' => $expiresAt,
            'duration_minutes' => $durationMinutes,
            'login_url' => $loginUrl,
            'login_log_id' => $loginLog->id,
            'message' => 'Temporary access link generated successfully',
        ];
    }

    /**
     * Scenario 8: Verify and process link login.
     * 
     * @param string $linkToken The temporary link token
     * @param string $macAddress Client MAC address
     * @param string|null $ipAddress Client IP address
     * @return array Login verification result
     */
    public function verifyLinkLogin(string $linkToken, string $macAddress, ?string $ipAddress = null): array
    {
        // Find login log by token
        $loginLog = HotspotLoginLog::where('link_token', $linkToken)
            ->where('is_link_login', true)
            ->first();

        if (!$loginLog) {
            return [
                'scenario' => 'link_login_invalid',
                'allow_login' => false,
                'message' => 'Invalid or expired link token',
                'action' => 'contact_support',
            ];
        }

        // Check if token is expired
        if ($loginLog->isLinkExpired()) {
            $loginLog->update(['status' => HotspotLoginLog::STATUS_EXPIRED]);
            
            return [
                'scenario' => 'link_login_expired',
                'allow_login' => false,
                'message' => 'This access link has expired',
                'action' => 'request_new_link',
                'expired_at' => $loginLog->link_expires_at,
            ];
        }

        // Update login log with device info
        $loginLog->update([
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress,
            'login_at' => now(),
        ]);

        return [
            'scenario' => 'link_login_success',
            'allow_login' => true,
            'login_log' => $loginLog,
            'session_id' => $loginLog->session_id,
            'expires_at' => $loginLog->link_expires_at,
            'message' => 'Public access granted',
            'action' => 'allow',
        ];
    }

    /**
     * Scenario 9: Handle logout and update tracking.
     * 
     * @param string $sessionId Session ID to logout
     * @param string $username Username (for RADIUS lookup)
     * @param array $sessionData Additional session data
     * @return array Logout result
     */
    public function handleLogout(string $sessionId, ?string $username = null, array $sessionData = []): array
    {
        // Find active login log
        $loginLog = HotspotLoginLog::where('session_id', $sessionId)
            ->active()
            ->first();

        if (!$loginLog) {
            return [
                'scenario' => 'logout_session_not_found',
                'success' => false,
                'message' => 'Active session not found',
            ];
        }

        // Mark session as logged out
        $loginLog->markAsLoggedOut();

        // Update radacct if username provided
        if ($username) {
            $this->updateRadacctOnLogout($username, $sessionData);
        }

        // Clear hotspot user session if exists
        if ($loginLog->hotspot_user_id) {
            $hotspotUser = $loginLog->hotspotUser;
            if ($hotspotUser && $hotspotUser->active_session_id === $sessionId) {
                $hotspotUser->clearSession();
            }
        }

        Log::info('User logged out', [
            'session_id' => $sessionId,
            'username' => $username,
            'duration' => $loginLog->session_duration,
        ]);

        return [
            'scenario' => 'logout_success',
            'success' => true,
            'session_id' => $sessionId,
            'login_at' => $loginLog->login_at,
            'logout_at' => $loginLog->logout_at,
            'duration' => $loginLog->session_duration,
            'message' => 'Logout successful',
        ];
    }

    /**
     * Update RADIUS accounting on logout.
     * 
     * @param string $username Username to update
     * @param array $sessionData Session data (bytes in/out, terminate cause, etc.)
     */
    private function updateRadacctOnLogout(string $username, array $sessionData = []): void
    {
        try {
            // Find active session in radacct
            $radacct = RadAcct::where('username', $username)
                ->whereNull('acctstoptime')
                ->orderBy('acctstarttime', 'desc')
                ->first();

            if ($radacct) {
                $stopTime = now();
                $sessionTime = $radacct->acctstarttime ? 
                    $stopTime->diffInSeconds($radacct->acctstarttime) : 0;

                $radacct->update([
                    'acctstoptime' => $stopTime,
                    'acctsessiontime' => $sessionTime,
                    'acctterminatecause' => $sessionData['terminate_cause'] ?? 'User-Request',
                    'acctinputoctets' => $sessionData['input_octets'] ?? $radacct->acctinputoctets ?? 0,
                    'acctoutputoctets' => $sessionData['output_octets'] ?? $radacct->acctoutputoctets ?? 0,
                ]);

                Log::info('RadAcct updated on logout', [
                    'username' => $username,
                    'session_time' => $sessionTime,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update radacct on logout', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Scenario 10: Cross-radius server lookup for federated authentication.
     * 
     * @param string $username Username to lookup
     * @param int|null $tenantId Current tenant ID
     * @return array Lookup result with home operator info
     */
    public function crossRadiusLookup(string $username, ?int $tenantId = null): array
    {
        // Extract realm from username if exists (user@realm format)
        $parts = explode('@', $username);
        $localUsername = $parts[0];
        $realm = $parts[1] ?? null;

        // Check if user exists in current tenant
        $localUser = $this->findCustomerByUsername($localUsername, $tenantId);

        if ($localUser) {
            // User found locally
            return [
                'scenario' => 'local_authentication',
                'allow_login' => true,
                'federated' => false,
                'customer' => $localUser,
                'message' => 'User authenticated locally',
                'action' => 'allow',
            ];
        }

        // If realm is specified, attempt federated lookup
        if ($realm) {
            $homeOperator = $this->findHomeOperator($realm);

            if ($homeOperator) {
                // Redirect to home operator
                return [
                    'scenario' => 'federated_authentication',
                    'allow_login' => false,
                    'federated' => true,
                    'home_operator' => $homeOperator,
                    'realm' => $realm,
                    'username' => $username,
                    'redirect_url' => $this->buildFederatedRedirectUrl($homeOperator, $username),
                    'message' => 'User belongs to another operator. Redirecting to home operator.',
                    'action' => 'redirect',
                ];
            }
        }

        // User not found locally or in federation
        return [
            'scenario' => 'user_not_found',
            'allow_login' => false,
            'federated' => false,
            'username' => $username,
            'message' => 'User not found in local or federated systems',
            'action' => 'contact_support',
        ];
    }

    /**
     * Find customer by username.
     */
    private function findCustomerByUsername(string $username, ?int $tenantId): ?NetworkUser
    {
        $query = NetworkUser::where('username', $username);
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->first();
    }

    /**
     * Find home operator by realm.
     */
    private function findHomeOperator(string $realm): ?array
    {
        // Query central registry for operator by realm
        // In a real multi-operator setup, this would query a central database
        $operator = DB::table('operator_registry')
            ->where('realm', $realm)
            ->where('is_active', true)
            ->first();

        if (!$operator) {
            return null;
        }

        return [
            'id' => $operator->id,
            'name' => $operator->name,
            'realm' => $operator->realm,
            'portal_url' => $operator->portal_url,
            'radius_server' => $operator->radius_server ?? null,
        ];
    }

    /**
     * Build federated redirect URL.
     */
    private function buildFederatedRedirectUrl(array $homeOperator, string $username): string
    {
        $baseUrl = $homeOperator['portal_url'] ?? '';
        $params = http_build_query([
            'username' => $username,
            'realm' => $homeOperator['realm'],
            'federated' => 'true',
        ]);

        return $baseUrl . '/hotspot/login?' . $params;
    }

    /**
     * Log federated login attempt.
     */
    public function logFederatedLogin(
        string $username,
        array $homeOperator,
        string $macAddress,
        ?string $ipAddress,
        ?int $tenantId
    ): HotspotLoginLog {
        return HotspotLoginLog::create([
            'tenant_id' => $tenantId,
            'username' => $username,
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress,
            'session_id' => Str::uuid()->toString(),
            'login_type' => HotspotLoginLog::TYPE_FEDERATED,
            'scenario' => 'federated_authentication',
            'home_operator_id' => $homeOperator['id'] ?? null,
            'federated_login' => true,
            'redirect_url' => $homeOperator['portal_url'] ?? null,
            'status' => HotspotLoginLog::STATUS_ACTIVE,
            'metadata' => [
                'home_operator' => $homeOperator,
            ],
        ]);
    }
