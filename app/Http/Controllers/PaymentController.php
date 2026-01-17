<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentGatewayService $paymentGatewayService;
    protected BillingService $billingService;

    public function __construct(
        PaymentGatewayService $paymentGatewayService,
        BillingService $billingService
    ) {
        $this->paymentGatewayService = $paymentGatewayService;
        $this->billingService = $billingService;
    }

    /**
     * Show payment page
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $paymentGateways = $invoice->tenant->paymentGateways()
            ->where('is_active', true)
            ->get();

        return view('payments.show', compact('invoice', 'paymentGateways'));
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        $this->authorize('pay', $invoice);

        // Get active gateways for validation
        $activeGateways = PaymentGateway::where('tenant_id', $invoice->tenant_id)
            ->where('is_active', true)
            ->pluck('slug')
            ->toArray();

        if (empty($activeGateways)) {
            return back()->withErrors(['error' => 'No payment gateways are currently available.']);
        }

        $validated = $request->validate([
            'gateway' => 'required|string|in:' . implode(',', $activeGateways),
        ]);

        try {
            $paymentData = $this->paymentGatewayService->initiatePayment(
                $invoice,
                $validated['gateway']
            );

            if ($request->expectsJson()) {
                return response()->json($paymentData);
            }

            return redirect($paymentData['payment_url']);
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'invoice_id' => $invoice->id,
                'gateway' => $validated['gateway'],
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to initiate payment. Please try again.']);
        }
    }

    /**
     * Handle payment webhook
     */
    public function webhook(Request $request, string $gateway): JsonResponse
    {
        try {
            $payload = $request->all();
            
            Log::info("Received webhook from {$gateway}", $payload);

            $success = $this->paymentGatewayService->processWebhook($gateway, $payload);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Webhook processed successfully' : 'Failed to process webhook',
            ], $success ? 200 : 400);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed for {$gateway}", [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Handle payment success callback
     */
    public function success(Request $request): RedirectResponse
    {
        $transactionId = $request->get('transaction_id');
        $gateway = $request->get('gateway');

        if ($transactionId && $gateway) {
            try {
                $verification = $this->paymentGatewayService->verifyPayment(
                    $transactionId,
                    $gateway,
                    auth()->user()->tenant_id
                );

                if ($verification['verified'] ?? false) {
                    return redirect()->route('payments.show', ['invoice' => $request->get('invoice_id')])
                        ->with('success', 'Payment successful!');
                }
            } catch (\Exception $e) {
                Log::error('Payment verification failed', [
                    'transaction_id' => $transactionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('info', 'Payment is being processed. Please wait for confirmation.');
    }

    /**
     * Handle payment failure callback
     */
    public function failure(Request $request): RedirectResponse
    {
        return back()->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Handle payment cancellation
     */
    public function cancel(Request $request): RedirectResponse
    {
        return back()->with('info', 'Payment was cancelled.');
    }

    /**
     * Manual payment recording (for cash/bank transfer)
     */
    public function recordManualPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('recordPayment', $invoice);

        $manualMethods = array_keys(config('payment.manual_methods', ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'check' => 'Check']));
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:' . implode(',', $manualMethods),
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validate amount doesn't exceed invoice total
        if ($validated['amount'] > $invoice->total_amount) {
            return back()
                ->withErrors(['amount' => 'The payment amount cannot exceed the invoice total amount.'])
                ->withInput();
        }

        try {
            $this->billingService->processPayment($invoice, [
                'amount' => $validated['amount'],
                'method' => $validated['payment_method'],
                'status' => 'completed',
                'transaction_id' => $validated['transaction_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return back()->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            Log::error('Manual payment recording failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to record payment. Please try again.']);
        }
    }
}
