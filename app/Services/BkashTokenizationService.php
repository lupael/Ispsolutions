<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BkashAgreement;
use App\Models\BkashToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Bkash Tokenization Service
 * 
 * Handles Bkash tokenization for one-click payments
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Bkash Tokenization
 * Reference: REFERENCE_SYSTEM_IMPLEMENTATION_TODO.md - Section 1.4
 */
class BkashTokenizationService
{
    /**
     * Bkash API configuration
     */
    protected string $baseUrl;
    protected string $appKey;
    protected string $appSecret;
    protected string $username;
    protected string $password;
    protected bool $sandboxMode;

    public function __construct()
    {
        $this->baseUrl = config('services.bkash.base_url', 'https://tokenized.pay.bka.sh/v1.2.0-beta');
        $this->appKey = config('services.bkash.app_key', '');
        $this->appSecret = config('services.bkash.app_secret', '');
        $this->username = config('services.bkash.username', '');
        $this->password = config('services.bkash.password', '');
        $this->sandboxMode = config('services.bkash.sandbox_mode', true);
    }

    /**
     * Create a tokenization agreement with Bkash
     *
     * @param User $user The user creating the agreement
     * @param string $msisdn Customer mobile number
     * @param string $callbackUrl Callback URL after agreement creation
     * @return array Result with agreement_id and payment_url
     */
    public function createAgreement(User $user, string $msisdn, string $callbackUrl): array
    {
        try {
            // Get authorization token
            $authToken = $this->getAuthToken();

            // Create agreement request
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$authToken}",
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/create", [
                'mode' => '0000', // Tokenization mode
                'payerReference' => $user->id,
                'callbackURL' => $callbackUrl,
                'agreementID' => $this->generateAgreementId($user),
                'amount' => '1.00', // Minimum amount for agreement
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => 'AGR-' . time(),
            ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to create Bkash agreement: ' . $response->body());
            }

            $data = $response->json();

            // Create agreement record
            $agreement = BkashAgreement::create([
                'user_id' => $user->id,
                'agreement_id' => $data['agreementID'] ?? $this->generateAgreementId($user),
                'payment_id' => $data['paymentID'] ?? null,
                'status' => 'pending',
                'customer_msisdn' => $msisdn,
                'metadata' => json_encode($data),
            ]);

            return [
                'success' => true,
                'agreement_id' => $agreement->agreement_id,
                'payment_id' => $data['paymentID'] ?? null,
                'bkash_url' => $data['bkashURL'] ?? null,
                'callback_url' => $data['callbackURL'] ?? $callbackUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Bkash agreement creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute agreement after customer authorization
     *
     * @param BkashAgreement $agreement The agreement to execute
     * @param string $paymentId Payment ID from Bkash callback
     * @return array Result with token and status
     */
    public function executeAgreement(BkashAgreement $agreement, string $paymentId): array
    {
        try {
            // Get authorization token
            $authToken = $this->getAuthToken();

            // Execute agreement
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$authToken}",
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentId,
            ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to execute Bkash agreement: ' . $response->body());
            }

            $data = $response->json();

            if (($data['statusCode'] ?? '') !== '0000') {
                throw new \Exception($data['statusMessage'] ?? 'Agreement execution failed');
            }

            // Mark agreement as active
            $agreement->update([
                'status' => 'active',
                'payment_id' => $paymentId,
                'created_time' => now(),
                'metadata' => json_encode($data),
            ]);

            // Create token record
            // Note: Store the agreement token from response if available, otherwise use payment ID
            $tokenValue = $data['agreementToken'] ?? $data['paymentID'];
            
            $token = BkashToken::create([
                'user_id' => $agreement->user_id,
                'bkash_agreement_id' => $agreement->id,
                'token' => $tokenValue,
                'token_type' => 'bearer',
                'customer_msisdn' => $agreement->customer_msisdn,
                'is_default' => ! BkashToken::where('user_id', $agreement->user_id)->exists(),
            ]);

            return [
                'success' => true,
                'agreement' => $agreement,
                'token' => $token,
                'message' => 'Agreement executed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Bkash agreement execution failed', [
                'agreement_id' => $agreement->id,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel an agreement
     *
     * @param BkashAgreement $agreement The agreement to cancel
     * @return array Result with cancellation status
     */
    public function cancelAgreement(BkashAgreement $agreement): array
    {
        try {
            // Get authorization token
            $authToken = $this->getAuthToken();

            // Cancel agreement
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$authToken}",
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/agreement/cancel", [
                'agreementID' => $agreement->agreement_id,
            ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to cancel Bkash agreement: ' . $response->body());
            }

            // Mark agreement as cancelled
            $agreement->markCancelled();

            return [
                'success' => true,
                'message' => 'Agreement cancelled successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Bkash agreement cancellation failed', [
                'agreement_id' => $agreement->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process payment using tokenization
     *
     * @param BkashToken $token The token to use for payment
     * @param float $amount Payment amount
     * @param string $invoiceNumber Merchant invoice number
     * @return array Result with payment status and transaction ID
     */
    public function processTokenizedPayment(BkashToken $token, float $amount, string $invoiceNumber): array
    {
        try {
            // Validate token
            if (! $token->isValid()) {
                throw new \Exception('Token is invalid or expired');
            }

            // Ensure agreement relationship is loaded
            $token->loadMissing('agreement');

            // Check if agreement is still active
            if (! $token->agreement || ! $token->agreement->isActive()) {
                throw new \Exception('Agreement is no longer active');
            }

            // Get authorization token
            $authToken = $this->getAuthToken();

            // Create payment
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$authToken}",
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/create", [
                'mode' => '0001', // Tokenized payment mode
                'payerReference' => $token->user_id,
                'agreementID' => $token->agreement->agreement_id,
                'amount' => (string) $amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $invoiceNumber,
            ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to create tokenized payment: ' . $response->body());
            }

            $data = $response->json();
            $paymentId = $data['paymentID'] ?? null;

            if (! $paymentId) {
                throw new \Exception('Payment ID not received from Bkash');
            }

            // Execute payment
            $executeResponse = Http::withHeaders([
                'Authorization' => "Bearer {$authToken}",
                'X-APP-Key' => $this->appKey,
            ])->post("{$this->baseUrl}/tokenized/checkout/execute", [
                'paymentID' => $paymentId,
            ]);

            if (! $executeResponse->successful()) {
                throw new \Exception('Failed to execute tokenized payment: ' . $executeResponse->body());
            }

            $executeData = $executeResponse->json();

            if (($executeData['statusCode'] ?? '') !== '0000') {
                throw new \Exception($executeData['statusMessage'] ?? 'Payment execution failed');
            }

            // Mark token as used
            $token->markUsed();

            return [
                'success' => true,
                'transaction_id' => $executeData['trxID'] ?? $paymentId,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'message' => 'Payment processed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Tokenized payment processing failed', [
                'token_id' => $token->id,
                'amount' => $amount,
                'invoice_number' => $invoiceNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Bkash authorization token
     *
     * @return string Authorization token
     */
    protected function getAuthToken(): string
    {
        // Check cache first
        $cacheKey = 'bkash_auth_token';
        $cachedToken = Cache::get($cacheKey);

        if ($cachedToken) {
            return $cachedToken;
        }

        // Get new token
        $response = Http::withHeaders([
            'username' => $this->username,
            'password' => $this->password,
        ])->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to get Bkash auth token: ' . $response->body());
        }

        $data = $response->json();
        $token = $data['id_token'] ?? null;

        if (! $token) {
            throw new \Exception('Auth token not received from Bkash');
        }

        // Cache for 50 minutes (expires in 1 hour)
        Cache::put($cacheKey, $token, now()->addMinutes(50));

        return $token;
    }

    /**
     * Generate a unique agreement ID
     *
     * @param User $user
     * @return string
     */
    protected function generateAgreementId(User $user): string
    {
        return 'AGR-' . $user->id . '-' . time();
    }
}
