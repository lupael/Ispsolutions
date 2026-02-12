<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HotspotLogin\RequestLoginOtpRequest;
use App\Http\Requests\HotspotLogin\VerifyLoginOtpRequest;
use App\Models\HotspotLoginLog;
use App\Models\HotspotUser;
use App\Services\HotspotScenarioDetectionService;
use App\Services\OtpService;
use App\Services\SmsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class HotspotLoginController extends Controller
{
    protected OtpService $otpService;
    protected HotspotScenarioDetectionService $scenarioService;
    protected SmsService $smsService;

    protected HotspotService $hotspotService;

    public function __construct(
        OtpService $otpService,
        HotspotScenarioDetectionService $scenarioService,
        SmsService $smsService,
        HotspotService $hotspotService
    ) {
        $this->otpService = $otpService;
        $this->scenarioService = $scenarioService;
        $this->smsService = $smsService;
        $this->hotspotService = $hotspotService;
    }

    /**
     * Show login form
     */
    public function showLoginForm(): View
    {
        return view('hotspot-login.login-form');
    }

    /**
     * Request OTP for login
     */
    public function requestLoginOtp(RequestLoginOtpRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $mobileNumber = $request->validated()['mobile_number'];
            $ipAddress = $request->ip();

            // Check if user exists and is active
            $hotspotUser = HotspotUser::where('phone_number', $mobileNumber)
                ->where('is_verified', true)
                ->first();

            if (!$hotspotUser) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This mobile number is not registered. Please sign up first.',
                    ], 400);
                }
                return back()->withErrors([
                    'mobile_number' => 'This mobile number is not registered. Please sign up first.',
                ])->withInput();
            }

            if ($hotspotUser->status !== 'active') {
                $message = 'Your account is currently ' . $hotspotUser->status . '. Please contact support.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 400);
                }
                return back()->withErrors([
                    'mobile_number' => $message,
                ])->withInput();
            }

            // Check if account is expired
            if ($hotspotUser->expires_at && $hotspotUser->expires_at->isPast()) {
                $message = 'Your account has expired. Please renew your subscription.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 400);
                }
                return back()->withErrors([
                    'mobile_number' => $message,
                ])->withInput();
            }

            // Get tenant ID
            $tenantId = $hotspotUser->tenant_id;

            // Generate and store OTP
            $otpData = $this->otpService->storeOtp($mobileNumber, $ipAddress, $tenantId);

            // Store login data in session
            session([
                'hotspot_login' => [
                    'mobile_number' => $mobileNumber,
                    'user_id' => $hotspotUser->id,
                    'otp_expires_at' => $otpData['expires_at']->timestamp,
                ],
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent to your mobile number. Please check your SMS.',
                    'expires_at' => $otpData['expires_at']->timestamp,
                ]);
            }

            return redirect()
                ->route('hotspot.login.verify-otp')
                ->with('success', 'OTP sent to your mobile number. Please check your SMS.');

        } catch (\Exception $e) {
            Log::error('Login OTP request failed', [
                'mobile_number' => $request->mobile_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Only show user-friendly errors, not technical details
            $userMessage = 'Unable to send OTP at this time. Please try again later.';

            // If it's an OtpService exception, it's already user-friendly
            if (Str::contains($e->getMessage(), 'OTP') || Str::contains($e->getMessage(), 'try again')) {
                $userMessage = $e->getMessage();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $userMessage,
                ], 400);
            }

            return back()
                ->withErrors(['error' => $userMessage])
                ->withInput();
        }
    }

    /**
     * Show OTP verification form for login
     */
    public function showVerifyLoginOtp(): View|RedirectResponse
    {
        $loginData = session('hotspot_login');

        if (!$loginData) {
            return redirect()
                ->route('hotspot.login')
                ->withErrors(['error' => 'Session expired. Please start again.']);
        }

        return view('hotspot-login.verify-otp', [
            'mobile_number' => $loginData['mobile_number'],
            'expires_at' => $loginData['otp_expires_at'],
        ]);
    }

    /**
     * Verify OTP and login user
     */
    public function verifyLoginOtp(VerifyLoginOtpRequest $request): RedirectResponse
    {
        try {
            $loginData = session('hotspot_login');

            if (!$loginData) {
                return redirect()
                    ->route('hotspot.login')
                    ->withErrors(['error' => 'Session expired. Please start again.']);
            }

            $mobileNumber = $request->validated()['mobile_number'];
            $otpCode = $request->validated()['otp_code'];
            $ipAddress = $request->ip();

            // Verify mobile number matches session
            if ($mobileNumber !== $loginData['mobile_number']) {
                return back()->withErrors([
                    'mobile_number' => 'Mobile number does not match.',
                ])->withInput();
            }

            // Verify OTP
            $this->otpService->verifyOtp($mobileNumber, $otpCode, $ipAddress);

            // Get hotspot user
            $hotspotUser = HotspotUser::findOrFail($loginData['user_id']);

            // Get MAC address from request
            $macAddress = $this->getMacAddress($request);

            // Check if user has active session on different device
            if ($hotspotUser->hasActiveSessionOnDifferentDevice($macAddress)) {
                // Store conflict info in session
                session(['hotspot_login.device_conflict' => true]);
                session(['hotspot_login.new_mac_address' => $macAddress]);

                return redirect()
                    ->route('hotspot.login.device-conflict')
                    ->with('warning', 'You are already logged in on another device.');
            }

            // Create new session and login
            $this->loginUser($hotspotUser, $macAddress, $request);

            return redirect()
                ->route('hotspot.dashboard')
                ->with('success', 'Login successful! Welcome back.');

        } catch (\Exception $e) {
            Log::error('Login OTP verification failed', [
                'mobile_number' => $request->mobile_number ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Only show user-friendly errors
            $userMessage = 'Unable to verify OTP. Please try again.';

            // If it's an OtpService exception, it's already user-friendly
            if (Str::contains($e->getMessage(), 'OTP') || Str::contains($e->getMessage(), 'attempts')) {
                $userMessage = $e->getMessage();
            }

            return back()
                ->withErrors(['otp_code' => $userMessage])
                ->withInput();
        }
    }

    /**
     * Show device conflict page
     */
    public function showDeviceConflict(): View|RedirectResponse
    {
        $loginData = session('hotspot_login');

        if (!$loginData || !isset($loginData['device_conflict'])) {
            return redirect()->route('hotspot.login');
        }

        $hotspotUser = HotspotUser::findOrFail($loginData['user_id']);

        return view('hotspot-login.device-conflict', [
            'user' => $hotspotUser,
            'current_mac' => $hotspotUser->mac_address,
            'new_mac' => $loginData['new_mac_address'] ?? 'unknown',
        ]);
    }

    /**
     * Force login by logging out from other device
     */
    public function forceLogin(Request $request): RedirectResponse
    {
        $loginData = session('hotspot_login');

        if (!$loginData || !isset($loginData['device_conflict'])) {
            return redirect()->route('hotspot.login');
        }

        try {
            $hotspotUser = HotspotUser::findOrFail($loginData['user_id']);
            $newMacAddress = $loginData['new_mac_address'];

            // Clear old session and login with new device
            $this->loginUser($hotspotUser, $newMacAddress, $request);

            // Clear conflict flag
            session()->forget('hotspot_login');

            return redirect()
                ->route('hotspot.dashboard')
                ->with('success', 'Login successful! Your previous device has been logged out.');

        } catch (\Exception $e) {
            Log::error('Force login failed', [
                'user_id' => $loginData['user_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Failed to login. Please try again.']);
        }
    }

    /**
     * Show dashboard for logged in users
     */
    public function showDashboard(Request $request): View|RedirectResponse
    {
        $hotspotUser = $this->getAuthenticatedUser($request);

        if (!$hotspotUser) {
            return redirect()
                ->route('hotspot.login')
                ->withErrors(['error' => 'Please login to access the dashboard.']);
        }

        return view('hotspot-login.dashboard', [
            'user' => $hotspotUser->load('package'),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): RedirectResponse
    {
        $hotspotUser = $this->getAuthenticatedUser($request);

        if ($hotspotUser) {
            $sessionId = session('hotspot_auth.session_id');

            // Use scenario service to handle logout (Scenario 9)
            if ($sessionId) {
                $this->scenarioService->handleLogout(
                    $sessionId,
                    $hotspotUser->username,
                    []
                );
            }

            // Clear session in database
            $hotspotUser->clearSession();

            Log::info('Hotspot user logged out', [
                'user_id' => $hotspotUser->id,
                'mac_address' => $hotspotUser->mac_address,
            ]);
        }

        // Clear session data
        session()->forget('hotspot_auth');
        session()->forget('hotspot_login');

        return redirect()
            ->route('hotspot.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Scenario 8: Generate link login (public access).
     */
    public function generateLinkLogin(Request $request): JsonResponse
    {
        $request->validate([
            'duration_minutes' => 'nullable|integer|min:1|max:1440',
            'metadata' => 'nullable|array',
        ]);

        try {
            $tenantId = auth()->user()?->tenant_id;
            $durationMinutes = $request->input('duration_minutes', 60);
            $metadata = $request->input('metadata', []);

            $result = $this->scenarioService->generateLinkLogin(
                $tenantId,
                $durationMinutes,
                $metadata
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate link login', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate access link',
            ], 500);
        }
    }

    /**
     * Scenario 8: Process link login.
     */
    public function processLinkLogin(string $token, Request $request): View|RedirectResponse
    {
        try {
            $macAddress = $this->getMacAddress($request);
            $ipAddress = $request->ip();

            $result = $this->scenarioService->verifyLinkLogin($token, $macAddress, $ipAddress);

            if (!$result['allow_login']) {
                return redirect()
                    ->route('hotspot.login')
                    ->withErrors(['error' => $result['message']]);
            }

            // Store session for link login
            session([
                'hotspot_auth' => [
                    'session_id' => $result['session_id'],
                    'mac_address' => $macAddress,
                    'logged_in_at' => now()->timestamp,
                    'is_link_login' => true,
                    'expires_at' => $result['expires_at']->timestamp,
                ],
            ]);

            return redirect()
                ->route('hotspot.link-dashboard')
                ->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Link login failed', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('hotspot.login')
                ->withErrors(['error' => 'Invalid or expired access link']);
        }
    }

    /**
     * Scenario 8: Show dashboard for link login users.
     */
    public function showLinkDashboard(Request $request): View|RedirectResponse
    {
        $authData = session('hotspot_auth');

        if (!$authData || !($authData['is_link_login'] ?? false)) {
            return redirect()
                ->route('hotspot.login')
                ->withErrors(['error' => 'Please login to access the dashboard.']);
        }

        // Check if link has expired
        if (isset($authData['expires_at']) && now()->timestamp > $authData['expires_at']) {
            session()->forget('hotspot_auth');

            return redirect()
                ->route('hotspot.login')
                ->withErrors(['error' => 'Your access link has expired.']);
        }

        return view('hotspot-login.link-dashboard', [
            'session_id' => $authData['session_id'],
            'expires_at' => $authData['expires_at'],
            'logged_in_at' => $authData['logged_in_at'],
        ]);
    }

    /**
     * Scenario 10: Handle federated login.
     */
    public function federatedLogin(Request $request): View|RedirectResponse|JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        try {
            $username = $request->input('username');
            $tenantId = auth()->user()?->tenant_id;
            $macAddress = $this->getMacAddress($request);
            $ipAddress = $request->ip();

            // Perform cross-radius lookup (Scenario 10)
            $result = $this->scenarioService->crossRadiusLookup($username, $tenantId);

            if ($result['federated'] ?? false) {
                // Log federated login attempt
                $this->scenarioService->logFederatedLogin(
                    $username,
                    $result['home_operator'],
                    $macAddress,
                    $ipAddress,
                    $tenantId
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'federated' => true,
                        'redirect_url' => $result['redirect_url'],
                        'message' => $result['message'],
                    ]);
                }

                return redirect()->away($result['redirect_url']);
            }

            // Local authentication
            if ($result['allow_login']) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'federated' => false,
                        'message' => $result['message'],
                    ]);
                }

                return redirect()
                    ->route('hotspot.login')
                    ->with('success', $result['message']);
            }

            // User not found
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 404);
            }

            return back()
                ->withErrors(['username' => $result['message']])
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Federated login failed', [
                'username' => $request->input('username'),
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Federated authentication failed',
                ], 500);
            }

            return back()
                ->withErrors(['error' => 'Federated authentication failed'])
                ->withInput();
        }
    }

    /**
     * Send SMS notification for device change.
     */
    protected function sendDeviceChangeSms(HotspotUser $user, string $oldMac, string $newMac): void
    {
        try {
            $message = sprintf(
                'Security Alert: Your device MAC address has changed from %s to %s. If this was not you, please contact support immediately.',
                $oldMac,
                $newMac
            );

            $this->smsService->sendSms(
                $user->phone_number,
                $message,
                null,
                null,
                $user->tenant_id
            );

            Log::info('Device change SMS sent', [
                'user_id' => $user->id,
                'old_mac' => $oldMac,
                'new_mac' => $newMac,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send device change SMS', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS notification for account suspension.
     */
    protected function sendSuspensionSms(HotspotUser $user, string $reason): void
    {
        try {
            $message = sprintf(
                'Your account has been suspended. Reason: %s. Please contact support for assistance.',
                $reason
            );

            $this->smsService->sendSms(
                $user->phone_number,
                $message,
                null,
                null,
                $user->tenant_id
            );

            Log::info('Suspension SMS sent', [
                'user_id' => $user->id,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send suspension SMS', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS notification for successful login.
     */
    protected function sendLoginSuccessSms(HotspotUser $user, string $macAddress): void
    {
        try {
            // Format MAC address for display (handle various formats)
            $macDisplay = $this->formatMacForDisplay($macAddress);

            $message = sprintf(
                'Login successful! Your device (%s) is now connected. Welcome back!',
                $macDisplay
            );

            $this->smsService->sendSms(
                $user->phone_number,
                $message,
                null,
                null,
                $user->tenant_id
            );

            Log::info('Login success SMS sent', [
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send login success SMS', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format MAC address for display in SMS.
     * Handles various formats and ensures consistent output.
     */
    protected function formatMacForDisplay(string $mac): string
    {
        // Remove any separators
        $cleaned = strtoupper(str_replace([':', '-', '.'], '', $mac));

        // Validate length (should be 12 hex characters)
        if (strlen($cleaned) !== 12 || !ctype_xdigit($cleaned)) {
            return 'Unknown Device';
        }

        // Format as XX:XX:XX:XX:XX:XX
        return implode(':', str_split($cleaned, 2));
    }

    /**
     * Login user and create session
     */
    protected function loginUser(HotspotUser $hotspotUser, string $macAddress, Request $request): void
    {
        // Generate unique session ID
        $sessionId = Str::uuid()->toString();

        // Update user's login session
        $hotspotUser->updateLoginSession($macAddress, $sessionId);

        // Sync to RADIUS
        $this->hotspotService->syncToRadius($hotspotUser);

        // Store auth data in session
        session([
            'hotspot_auth' => [
                'user_id' => $hotspotUser->id,
                'session_id' => $sessionId,
                'mac_address' => $macAddress,
                'logged_in_at' => now()->timestamp,
            ],
        ]);

        Log::info('Hotspot user logged in', [
            'user_id' => $hotspotUser->id,
            'mac_address' => $macAddress,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Get authenticated user from session
     */
    protected function getAuthenticatedUser(Request $request): ?HotspotUser
    {
        $authData = session('hotspot_auth');

        if (!$authData) {
            return null;
        }

        $hotspotUser = HotspotUser::find($authData['user_id']);

        if (!$hotspotUser) {
            return null;
        }

        // Verify session ID matches
        if ($hotspotUser->active_session_id !== $authData['session_id']) {
            // Session has been invalidated
            session()->forget('hotspot_auth');
            return null;
        }

        // Verify MAC address matches
        $currentMac = $this->getMacAddress($request);
        if ($hotspotUser->mac_address !== $currentMac) {
            // Different device
            session()->forget('hotspot_auth');
            return null;
        }

        return $hotspotUser;
    }

    /**
     * Get MAC address from request
     * In real hotspot scenarios, this would come from RADIUS or router
     * For web-based login, we use a combination of factors as identifier
     */
    protected function getMacAddress(Request $request): string
    {
        // In a real hotspot system, MAC address would be provided by the router
        // For this implementation, we'll use a fingerprint based on:
        // - IP address
        // - User agent
        // This creates a unique identifier for each device

        $fingerprint = $request->ip() . '|' . $request->userAgent();

        // Create a hash that looks like a MAC address format
        $hash = md5($fingerprint);
        $mac = substr($hash, 0, 2) . ':' .
               substr($hash, 2, 2) . ':' .
               substr($hash, 4, 2) . ':' .
               substr($hash, 6, 2) . ':' .
               substr($hash, 8, 2) . ':' .
               substr($hash, 10, 2);

        return strtoupper($mac);
    }
}
