<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HotspotSelfSignup\CompleteRegistrationRequest;
use App\Http\Requests\HotspotSelfSignup\HotspotPaymentRequest;
use App\Http\Requests\HotspotSelfSignup\RequestOtpRequest;
use App\Http\Requests\HotspotSelfSignup\VerifyOtpRequest;
use App\Models\HotspotUser;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\HotspotService;
use App\Services\OtpService;
use App\Services\PaymentGatewayService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HotspotSelfSignupController extends Controller
{
    protected OtpService $otpService;
    protected HotspotService $hotspotService;
    protected PaymentGatewayService $paymentGatewayService;
    protected SmsService $smsService;

    public function __construct(
        OtpService $otpService,
        HotspotService $hotspotService,
        PaymentGatewayService $paymentGatewayService,
        SmsService $smsService
    ) {
        $this->otpService = $otpService;
        $this->hotspotService = $hotspotService;
        $this->paymentGatewayService = $paymentGatewayService;
        $this->smsService = $smsService;
    }

    /**
     * Show registration form (Step 1)
     */
    public function showRegistrationForm()
    {
        // Get active packages
        $packages = Package::where('status', 'active')
            ->orderBy('price', 'asc')
            ->get();

        return view('hotspot-signup.registration-form', compact('packages'));
    }

    /**
     * Request OTP (Step 2)
     */
    public function requestOtp(RequestOtpRequest $request)
    {
        try {
            $mobileNumber = $request->validated()['mobile_number'];
            $packageId = $request->validated()['package_id'];
            $ipAddress = $request->ip();

            // Check if user already exists and is active
            $existingUser = HotspotUser::where('phone_number', $mobileNumber)
                ->where('is_verified', true)
                ->where('status', 'active')
                ->first();

            if ($existingUser) {
                return back()->withErrors([
                    'mobile_number' => 'This mobile number is already registered. Please contact support for assistance.',
                ])->withInput();
            }

            // Get tenant ID (default tenant or from subdomain)
            $tenantId = $this->getTenantId($request);

            // Generate and store OTP
            $otpData = $this->otpService->storeOtp($mobileNumber, $ipAddress, $tenantId);

            // Store signup data in session
            session([
                'hotspot_signup' => [
                    'mobile_number' => $mobileNumber,
                    'package_id' => $packageId,
                    'otp_expires_at' => $otpData['expires_at']->timestamp,
                    'tenant_id' => $tenantId,
                ]
            ]);

            return redirect()
                ->route('hotspot.signup.verify-otp')
                ->with('success', 'OTP sent to your mobile number. Please check your SMS.');

        } catch (\Exception $e) {
            Log::error('OTP request failed', [
                'mobile_number' => $request->mobile_number,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show OTP verification form (Step 3)
     */
    public function showVerifyOtp()
    {
        $signupData = session('hotspot_signup');

        if (!$signupData) {
            return redirect()
                ->route('hotspot.signup')
                ->withErrors(['error' => 'Session expired. Please start again.']);
        }

        return view('hotspot-signup.verify-otp', [
            'mobile_number' => $signupData['mobile_number'],
            'expires_at' => $signupData['otp_expires_at'],
        ]);
    }

    /**
     * Verify OTP (Step 4)
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $signupData = session('hotspot_signup');

            if (!$signupData) {
                return redirect()
                    ->route('hotspot.signup')
                    ->withErrors(['error' => 'Session expired. Please start again.']);
            }

            $mobileNumber = $request->validated()['mobile_number'];
            $otpCode = $request->validated()['otp_code'];
            $ipAddress = $request->ip();

            // Verify mobile number matches session
            if ($mobileNumber !== $signupData['mobile_number']) {
                return back()->withErrors([
                    'mobile_number' => 'Mobile number does not match.',
                ])->withInput();
            }

            // Verify OTP
            $this->otpService->verifyOtp($mobileNumber, $otpCode, $ipAddress);

            // Mark as verified in session
            session(['hotspot_signup.otp_verified' => true]);

            return redirect()
                ->route('hotspot.signup.complete')
                ->with('success', 'OTP verified successfully!');

        } catch (\Exception $e) {
            Log::error('OTP verification failed', [
                'mobile_number' => $request->mobile_number,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['otp_code' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        try {
            $signupData = session('hotspot_signup');

            if (!$signupData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please start again.',
                ], 400);
            }

            $mobileNumber = $signupData['mobile_number'];
            $ipAddress = $request->ip();
            $tenantId = $signupData['tenant_id'] ?? null;

            // Resend OTP
            $otpData = $this->otpService->resendOtp($mobileNumber, $ipAddress, $tenantId);

            // Update session
            session(['hotspot_signup.otp_expires_at' => $otpData['expires_at']->timestamp]);

            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent to your mobile number.',
                'expires_at' => $otpData['expires_at']->timestamp,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show complete profile form (Step 5)
     */
    public function showCompleteProfile()
    {
        $signupData = session('hotspot_signup');

        if (!$signupData || !$signupData['otp_verified']) {
            return redirect()
                ->route('hotspot.signup')
                ->withErrors(['error' => 'Please verify OTP first.']);
        }

        $package = Package::find($signupData['package_id']);

        return view('hotspot-signup.complete-profile', [
            'mobile_number' => $signupData['mobile_number'],
            'package' => $package,
        ]);
    }

    /**
     * Complete registration and create account (Step 6)
     */
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        try {
            $signupData = session('hotspot_signup');

            if (!$signupData || !$signupData['otp_verified']) {
                return redirect()
                    ->route('hotspot.signup')
                    ->withErrors(['error' => 'Please verify OTP first.']);
            }

            $validated = $request->validated();
            $mobileNumber = $validated['mobile_number'];

            // Verify mobile number matches session
            if ($mobileNumber !== $signupData['mobile_number']) {
                return back()->withErrors([
                    'mobile_number' => 'Mobile number does not match.',
                ])->withInput();
            }

            // Create or update hotspot user
            $hotspotUser = DB::transaction(function () use ($signupData, $validated) {
                $package = Package::findOrFail($signupData['package_id']);
                $username = $this->generateUsername($signupData['mobile_number']);
                $password = Str::random(8);

                $hotspotUser = HotspotUser::updateOrCreate(
                    [
                        'phone_number' => $signupData['mobile_number'],
                    ],
                    [
                        'tenant_id' => $signupData['tenant_id'],
                        'username' => $username,
                        'name' => $validated['name'],
                        'email' => $validated['email'] ?? null,
                        'address' => $validated['address'] ?? null,
                        'password' => Hash::make($password),
                        'package_id' => $signupData['package_id'],
                        'status' => 'pending_payment',
                        'is_verified' => true,
                        'verified_at' => now(),
                    ]
                );

                // Store plain password temporarily
                $hotspotUser->plain_password = $password;

                return $hotspotUser;
            });

            // Store user data in session for payment
            session(['hotspot_signup.user_id' => $hotspotUser->id]);
            session(['hotspot_signup.user_data' => $validated]);

            return redirect()
                ->route('hotspot.signup.payment', ['user' => $hotspotUser->id])
                ->with('success', 'Account created! Please complete payment to activate.');

        } catch (\Exception $e) {
            Log::error('Registration completion failed', [
                'mobile_number' => $request->mobile_number,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Show payment page (Step 7)
     */
    public function showPaymentPage(Request $request, $userId)
    {
        $signupData = session('hotspot_signup');

        if (!$signupData || $signupData['user_id'] !== (int) $userId) {
            return redirect()
                ->route('hotspot.signup')
                ->withErrors(['error' => 'Invalid session. Please start again.']);
        }

        $hotspotUser = HotspotUser::findOrFail($userId);
        $package = Package::findOrFail($hotspotUser->package_id);
        
        // Get active payment gateways for the tenant
        $paymentGateways = PaymentGateway::where('tenant_id', $hotspotUser->tenant_id)
            ->where('is_active', true)
            ->get();

        return view('hotspot-signup.payment', [
            'user' => $hotspotUser,
            'package' => $package,
            'paymentGateways' => $paymentGateways,
        ]);
    }

    /**
     * Process payment (Step 8)
     */
    public function processPayment(HotspotPaymentRequest $request, $userId)
    {
        try {
            $signupData = session('hotspot_signup');

            if (!$signupData || $signupData['user_id'] !== (int) $userId) {
                return redirect()
                    ->route('hotspot.signup')
                    ->withErrors(['error' => 'Invalid session. Please start again.']);
            }

            $hotspotUser = HotspotUser::findOrFail($userId);
            $package = Package::findOrFail($hotspotUser->package_id);
            $gatewaySlug = $request->validated()['payment_gateway'];

            // Create invoice for the payment
            $invoice = DB::transaction(function () use ($hotspotUser, $package) {
                return Invoice::create([
                    'tenant_id' => $hotspotUser->tenant_id,
                    'network_user_id' => null, // Hotspot user, not network user
                    'invoice_number' => 'HS-' . strtoupper(Str::random(8)),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(1),
                    'subtotal' => $package->price,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $package->price,
                    'status' => 'pending',
                    'notes' => "Hotspot package: {$package->name}",
                ]);
            });

            // Initiate payment
            $paymentData = $this->paymentGatewayService->initiatePayment(
                $invoice,
                $gatewaySlug,
                [
                    'hotspot_user_id' => $hotspotUser->id,
                    'mobile_number' => $hotspotUser->phone_number,
                ]
            );

            // Store payment reference in session
            session(['hotspot_signup.invoice_id' => $invoice->id]);

            // Redirect to payment gateway
            if (isset($paymentData['redirect_url'])) {
                return redirect($paymentData['redirect_url']);
            }

            return view('hotspot-signup.payment-processing', [
                'paymentData' => $paymentData,
                'gateway' => $gatewaySlug,
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Payment initiation failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Payment callback/webhook handler (Step 9)
     */
    public function paymentCallback(Request $request)
    {
        try {
            $invoiceId = session('hotspot_signup.invoice_id');
            
            if (!$invoiceId) {
                return redirect()
                    ->route('hotspot.signup.error')
                    ->with('error', 'Invalid payment session.');
            }

            $invoice = Invoice::findOrFail($invoiceId);
            
            // Check if payment is successful
            if ($invoice->status === 'paid') {
                // Activate hotspot account
                $this->activateHotspotAccount($invoice);

                return redirect()->route('hotspot.signup.success');
            }

            return redirect()
                ->route('hotspot.signup.error')
                ->with('error', 'Payment verification failed. Please contact support.');

        } catch (\Exception $e) {
            Log::error('Payment callback failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('hotspot.signup.error')
                ->with('error', 'Payment processing failed.');
        }
    }

    /**
     * Show success page (Step 10)
     */
    public function showSuccess()
    {
        $signupData = session('hotspot_signup');

        if (!$signupData || !isset($signupData['user_id'])) {
            return redirect()->route('hotspot.signup');
        }

        $hotspotUser = HotspotUser::find($signupData['user_id']);

        if (!$hotspotUser || $hotspotUser->status !== 'active') {
            return redirect()->route('hotspot.signup');
        }

        // Clear signup session
        session()->forget('hotspot_signup');

        return view('hotspot-signup.success', [
            'user' => $hotspotUser,
        ]);
    }

    /**
     * Show error page
     */
    public function showError()
    {
        return view('hotspot-signup.error');
    }

    /**
     * Activate hotspot account after successful payment
     */
    protected function activateHotspotAccount(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            // Find hotspot user from session or invoice metadata
            $signupData = session('hotspot_signup');
            
            if (!$signupData) {
                throw new \Exception('Signup session not found');
            }

            $hotspotUser = HotspotUser::findOrFail($signupData['user_id']);
            $package = Package::findOrFail($hotspotUser->package_id);

            // Calculate expiration
            $validityDays = $package->validity_days ?? 30;
            $expiresAt = now()->addDays($validityDays);

            // Activate account
            $hotspotUser->update([
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);

            // Send SMS with credentials
            $this->sendActivationSms($hotspotUser);

            Log::info('Hotspot account activated', [
                'user_id' => $hotspotUser->id,
                'invoice_id' => $invoice->id,
            ]);
        });
    }

    /**
     * Send activation SMS with credentials
     */
    protected function sendActivationSms(HotspotUser $hotspotUser): void
    {
        $package = Package::find($hotspotUser->package_id);
        $message = "Welcome to Hotspot!\n\n";
        $message .= "Your account is now active.\n";
        $message .= "Username: {$hotspotUser->username}\n";
        
        if (isset($hotspotUser->plain_password)) {
            $message .= "Password: {$hotspotUser->plain_password}\n";
        }
        
        $message .= "\nPackage: {$package->name}\n";
        $message .= "Valid until: " . $hotspotUser->expires_at->format('d M Y') . "\n";
        $message .= "\nThank you!";

        try {
            $this->smsService->sendSms(
                $hotspotUser->phone_number,
                $message,
                null,
                null,
                $hotspotUser->tenant_id
            );
        } catch (\Exception $e) {
            Log::error('Failed to send activation SMS', [
                'user_id' => $hotspotUser->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate unique username
     */
    protected function generateUsername(string $phoneNumber): string
    {
        $base = preg_replace('/[^0-9]/', '', $phoneNumber);
        $base = substr($base, -8);
        $username = 'HS' . $base;

        $counter = 1;
        $originalUsername = $username;
        while (HotspotUser::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get tenant ID from request
     */
    protected function getTenantId(Request $request): ?int
    {
        // Try to get from subdomain or default tenant
        // This should be implemented based on your multi-tenancy setup
        return 1; // Default tenant for now
    }
}
