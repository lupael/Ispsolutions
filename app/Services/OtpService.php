<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OtpService
{
    protected SmsService $smsService;

    const OTP_LENGTH = 6;
    const OTP_EXPIRY_MINUTES = 5;
    const MAX_ATTEMPTS = 3;
    const RESEND_COOLDOWN_SECONDS = 60;
    const MAX_REQUESTS_PER_HOUR = 3;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Generate a 6-digit OTP
     */
    public function generateOtp(): string
    {
        // Use crypto_rand for better randomness
        $bytes = random_bytes(3);
        $number = hexdec(bin2hex($bytes)) % 1000000;
        return str_pad((string) $number, self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Store OTP with expiration and rate limiting
     */
    public function storeOtp(string $mobileNumber, ?string $ipAddress = null, ?int $tenantId = null): array
    {
        // Check rate limiting
        if (!$this->checkRateLimit($mobileNumber)) {
            throw new \Exception('Too many OTP requests. Please try again after 1 hour.');
        }

        // Check resend cooldown
        if (!$this->checkResendCooldown($mobileNumber)) {
            throw new \Exception('Please wait ' . self::RESEND_COOLDOWN_SECONDS . ' seconds before requesting a new OTP.');
        }

        // Generate OTP
        $otpCode = $this->generateOtp();
        
        // Invalidate any existing OTPs for this number
        Otp::where('mobile_number', $mobileNumber)
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]); // Mark as used

        // Store new OTP
        $otp = Otp::create([
            'mobile_number' => $mobileNumber,
            'otp' => Hash::make($otpCode),
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'ip_address' => $ipAddress,
            'attempts' => 0,
            'tenant_id' => $tenantId,
        ]);

        // Update rate limiting counters
        $this->incrementRateLimitCounter($mobileNumber);
        $this->setResendCooldown($mobileNumber);

        // Send OTP via SMS
        $this->sendOtpSms($mobileNumber, $otpCode);

        Log::info('OTP generated', [
            'mobile_number' => $mobileNumber,
            'ip_address' => $ipAddress,
            'otp_id' => $otp->id,
        ]);

        return [
            'otp_id' => $otp->id,
            'expires_at' => $otp->expires_at,
            'plain_otp' => config('app.debug') ? $otpCode : null, // Only in debug mode
        ];
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $mobileNumber, string $otpCode, ?string $ipAddress = null): bool
    {
        // Get the latest valid OTP
        $otp = Otp::where('mobile_number', $mobileNumber)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            Log::warning('OTP verification failed: No valid OTP found', [
                'mobile_number' => $mobileNumber,
                'ip_address' => $ipAddress,
            ]);
            throw new \Exception('Invalid or expired OTP. Please request a new one.');
        }

        // Check max attempts
        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            Log::warning('OTP verification failed: Max attempts exceeded', [
                'mobile_number' => $mobileNumber,
                'attempts' => $otp->attempts,
            ]);
            throw new \Exception('Maximum verification attempts exceeded. Please request a new OTP.');
        }

        // Increment attempts
        $otp->increment('attempts');

        // Verify OTP
        if (!Hash::check($otpCode, $otp->otp)) {
            $remainingAttempts = self::MAX_ATTEMPTS - $otp->attempts;
            Log::warning('OTP verification failed: Invalid code', [
                'mobile_number' => $mobileNumber,
                'remaining_attempts' => $remainingAttempts,
            ]);
            throw new \Exception("Invalid OTP code. {$remainingAttempts} attempts remaining.");
        }

        // Mark as verified
        $otp->update(['verified_at' => now()]);

        Log::info('OTP verified successfully', [
            'mobile_number' => $mobileNumber,
            'otp_id' => $otp->id,
        ]);

        return true;
    }

    /**
     * Resend OTP
     */
    public function resendOtp(string $mobileNumber, ?string $ipAddress = null, ?int $tenantId = null): array
    {
        return $this->storeOtp($mobileNumber, $ipAddress, $tenantId);
    }

    /**
     * Check rate limit (max requests per hour)
     */
    protected function checkRateLimit(string $mobileNumber): bool
    {
        $cacheKey = "otp_rate_limit:{$mobileNumber}";
        $count = Cache::get($cacheKey, 0);

        return $count < self::MAX_REQUESTS_PER_HOUR;
    }

    /**
     * Increment rate limit counter
     */
    protected function incrementRateLimitCounter(string $mobileNumber): void
    {
        $cacheKey = "otp_rate_limit:{$mobileNumber}";
        $count = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $count + 1, now()->addHour());
    }

    /**
     * Check resend cooldown
     */
    protected function checkResendCooldown(string $mobileNumber): bool
    {
        $cacheKey = "otp_resend_cooldown:{$mobileNumber}";
        return !Cache::has($cacheKey);
    }

    /**
     * Set resend cooldown
     */
    protected function setResendCooldown(string $mobileNumber): void
    {
        $cacheKey = "otp_resend_cooldown:{$mobileNumber}";
        Cache::put($cacheKey, true, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));
    }

    /**
     * Send OTP via SMS
     */
    protected function sendOtpSms(string $mobileNumber, string $otpCode): void
    {
        $message = "Your verification code is: {$otpCode}\n\nThis code will expire in " . self::OTP_EXPIRY_MINUTES . " minutes.\n\nDo not share this code with anyone.";

        try {
            // Try to send via SMS service
            $this->smsService->sendSms($mobileNumber, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP SMS', [
                'mobile_number' => $mobileNumber,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception - OTP is still stored
        }
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpiredOtps(): int
    {
        return Otp::where('expires_at', '<', now()->subDays(7))
            ->delete();
    }

    /**
     * Check if mobile number has verified OTP in session
     */
    public function hasVerifiedOtp(string $mobileNumber): bool
    {
        return Otp::where('mobile_number', $mobileNumber)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>', now()->subMinutes(30))
            ->exists();
    }
}
