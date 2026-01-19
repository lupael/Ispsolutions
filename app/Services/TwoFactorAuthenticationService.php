<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA for a user and generate secret key.
     */
    public function enable2FA(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => false, // Only enable after verification
        ]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'ISP Solution'),
            $user->email,
            $secret
        );

        return [
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ];
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable2FA(User $user): bool
    {
        return $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    /**
     * Verify 2FA code.
     */
    public function verify2FACode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);
        
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Verify and enable 2FA after initial setup.
     */
    public function verifyAndEnable(User $user, string $code): bool
    {
        if ($this->verify2FACode($user, $code)) {
            $user->update(['two_factor_enabled' => true]);
            return true;
        }

        return false;
    }

    /**
     * Generate recovery codes.
     */
    public function generateRecoveryCodes(User $user): Collection
    {
        $recoveryCodes = Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);
        });

        $user->update([
            'two_factor_recovery_codes' => encrypt($recoveryCodes->toJson()),
        ]);

        return $recoveryCodes;
    }

    /**
     * Verify recovery code.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = collect(json_decode(decrypt($user->two_factor_recovery_codes), true));

        if ($recoveryCodes->contains($code)) {
            // Remove used code
            $remainingCodes = $recoveryCodes->reject(fn($c) => $c === $code);
            
            $user->update([
                'two_factor_recovery_codes' => $remainingCodes->isEmpty() 
                    ? null 
                    : encrypt($remainingCodes->toJson()),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled && $user->two_factor_secret !== null;
    }

    /**
     * Get remaining recovery codes count.
     */
    public function getRemainingRecoveryCodesCount(User $user): int
    {
        if (!$user->two_factor_recovery_codes) {
            return 0;
        }

        return count(json_decode(decrypt($user->two_factor_recovery_codes), true));
    }
}
