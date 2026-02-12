<?php

namespace App\Services;

use App\Models\HotspotUser;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HotspotService
{
    private $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    public function syncToRadius(HotspotUser $hotspotUser): bool
    {
        if (empty($hotspotUser->mac_address)) {
            return false;
        }

        return $this->radiusService->createUser(
            $hotspotUser->mac_address,
            $hotspotUser->mac_address,
            [
                'Simultaneous-Use' => 1,
            ]
        );
    }

    /**
     * Generate and send OTP for hotspot user signup
     */
    public function sendOtpForSignup(string $phoneNumber, int $tenantId): HotspotUser
    {
        $otpCode = $this->generateOtp();
        $expiresAt = now()->addMinutes(10);

        // Find or create hotspot user
        $hotspotUser = HotspotUser::firstOrCreate(
            [
                'phone_number' => $phoneNumber,
                'tenant_id' => $tenantId,
            ],
            [
                'username' => $this->generateUsername($phoneNumber),
                'status' => 'pending',
                'is_verified' => false,
            ]
        );

        // Update OTP
        $hotspotUser->update([
            'otp_code' => Hash::make($otpCode),
            'otp_expires_at' => $expiresAt,
        ]);

        // Send OTP via SMS in production
        if (config('sms.enabled', false)) {
            $smsService = app(SmsService::class);
            $smsService->sendOtpSms($phoneNumber, $otpCode);
        }

        // Return user with plain OTP for testing
        $hotspotUser->plain_otp = $otpCode;

        return $hotspotUser;
    }

    /**
     * Generate a standalone OTP code (returns just the string)
     */
    public function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify OTP and activate hotspot user
     */
    public function verifyOTP(string $phoneNumber, string $otpCode, int $packageId, int $tenantId): HotspotUser
    {
        $hotspotUser = HotspotUser::where('phone_number', $phoneNumber)
            ->where('tenant_id', $tenantId)
            ->where('is_verified', false)
            ->firstOrFail();

        // Check if OTP is expired
        if (! $hotspotUser->isOtpValid()) {
            throw new \Exception('OTP has expired. Please request a new one.');
        }

        // Verify OTP
        if (! Hash::check($otpCode, $hotspotUser->otp_code)) {
            throw new \Exception('Invalid OTP code.');
        }

        // Activate user
        return $this->activateHotspotUser($hotspotUser, $packageId);
    }

    /**
     * Activate hotspot user with package
     */
    protected function activateHotspotUser(HotspotUser $hotspotUser, int $packageId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUser, $packageId) {
            $package = Package::findOrFail($packageId);

            // Calculate expiration based on package
            $validityDays = $package->validity_days ?? 30;
            $expiresAt = now()->addDays($validityDays);

            // Generate password
            $password = Str::random(8);

            $hotspotUser->update([
                'password' => Hash::make($password),
                'package_id' => $packageId,
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
                'expires_at' => $expiresAt,
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

            // Store plain password for response (should be sent via SMS)
            $hotspotUser->plain_password = $password;

            return $hotspotUser;
        });
    }

    /**
     * Create hotspot user manually (by admin)
     */
    public function createHotspotUser(array $data): HotspotUser
    {
        return DB::transaction(function () use ($data) {
            $password = $data['password'] ?? Str::random(8);

            $package = Package::findOrFail($data['package_id']);
            $validityDays = $package->validity_days ?? 30;

            // Support both 'phone_number' and 'mobile' fields for compatibility
            // The model has a 'mobile' accessor that maps to 'phone_number'
            $phoneNumber = $data['phone_number'] ?? $data['mobile'] ?? null;
            if (! $phoneNumber) {
                throw new \InvalidArgumentException('Phone number is required');
            }

            $hotspotUser = HotspotUser::create([
                'tenant_id' => $data['tenant_id'],
                'phone_number' => $phoneNumber,
                'username' => $data['username'] ?? $this->generateUsername($phoneNumber),
                'password' => Hash::make($password),
                'package_id' => $data['package_id'],
                'is_verified' => true,
                'verified_at' => now(),
                'status' => 'active',
                'expires_at' => now()->addDays($validityDays),
            ]);

            $hotspotUser->plain_password = $password;

            return $hotspotUser;
        });
    }

    /**
     * Suspend hotspot user by ID
     */
    public function suspendHotspotUser(int $hotspotUserId): bool
    {
        $hotspotUser = HotspotUser::findOrFail($hotspotUserId);
        $hotspotUser->update([
            'status' => 'suspended',
        ]);

        return true;
    }

    /**
     * Reactivate hotspot user by ID
     */
    public function reactivateHotspotUser(int $hotspotUserId): bool
    {
        $hotspotUser = HotspotUser::findOrFail($hotspotUserId);
        $hotspotUser->update([
            'status' => 'active',
        ]);

        return true;
    }

    /**
     * Renew hotspot user subscription by IDs
     */
    public function renewSubscription(int $hotspotUserId, int $packageId): bool
    {
        return DB::transaction(function () use ($hotspotUserId, $packageId) {
            $hotspotUser = HotspotUser::findOrFail($hotspotUserId);
            $package = Package::findOrFail($packageId);
            $validityDays = $package->validity_days ?? 30;

            $currentExpiry = $hotspotUser->expires_at && $hotspotUser->expires_at->isFuture()
                ? $hotspotUser->expires_at
                : now();

            // Use copy() to avoid mutating the original Carbon instance
            $newExpiry = $currentExpiry->copy()->addDays($validityDays);

            $hotspotUser->update([
                'package_id' => $packageId,
                'status' => 'active',
                'expires_at' => $newExpiry,
            ]);

            return true;
        });
    }

    /**
     * Get expired hotspot users
     */
    public function getExpiredUsers()
    {
        return HotspotUser::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();
    }

    /**
     * Renew hotspot user subscription (model-based, legacy support)
     */
    protected function renewSubscriptionLegacy(HotspotUser $hotspotUser, int $packageId): HotspotUser
    {
        return DB::transaction(function () use ($hotspotUser, $packageId) {
            $package = Package::findOrFail($packageId);
            $validityDays = $package->validity_days ?? 30;

            $currentExpiry = $hotspotUser->expires_at && $hotspotUser->expires_at->isFuture()
                ? $hotspotUser->expires_at
                : now();

            $hotspotUser->update([
                'package_id' => $packageId,
                'status' => 'active',
                'expires_at' => $currentExpiry->addDays($validityDays),
            ]);

            return $hotspotUser;
        });
    }

    /**
     * Suspend hotspot user
     */
    public function suspend(HotspotUser $hotspotUser, ?string $reason = null): HotspotUser
    {
        $hotspotUser->update([
            'status' => 'suspended',
        ]);

        return $hotspotUser;
    }

    /**
     * Reactivate hotspot user
     */
    public function reactivate(HotspotUser $hotspotUser): HotspotUser
    {
        $hotspotUser->update([
            'status' => 'active',
        ]);

        return $hotspotUser;
    }

    /**
     * Deactivate expired hotspot users
     */
    public function deactivateExpiredUsers(int $tenantId): int
    {
        return HotspotUser::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Generate unique username from phone number
     */
    public function generateUsername(string $phoneNumber): string
    {
        $base = preg_replace('/[^0-9]/', '', $phoneNumber);
        $base = substr($base, -8); // Last 8 digits

        $username = 'hs_' . $phoneNumber;

        // Ensure uniqueness
        $counter = 1;
        $originalUsername = $username;
        while (HotspotUser::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get hotspot user statistics
     */
    public function getUserStats(int $tenantId): array
    {
        $query = HotspotUser::where('tenant_id', $tenantId);

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'suspended' => (clone $query)->where('status', 'suspended')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'verified' => (clone $query)->where('is_verified', true)->count(),
            'unverified' => (clone $query)->where('is_verified', false)->count(),
        ];
    }

    /**
     * Search hotspot users
     */
    public function searchUsers(int $tenantId, ?string $search = null, ?string $status = null)
    {
        $query = HotspotUser::where('tenant_id', $tenantId)
            ->with(['package']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate(20);
    }
}
