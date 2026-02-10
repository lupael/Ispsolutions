# Payment Gateway Integration Guide

This guide provides instructions for completing the payment gateway integration in the customer panel.

## Current Status

### ✅ Completed
- `PaymentGateway` model
- `PaymentGatewayService` with all gateway implementations
- Backend API integrations (bKash, Nagad, SSLCommerz, Stripe, Razorpay)
- Webhook handling

### ⏳ Pending
- Customer-facing payment initiation views
- Payment confirmation pages
- Payment status tracking UI

## Implementation Steps

### Step 1: Add Payment Routes

Add these routes to `routes/web.php` in the customer panel section:

```php
Route::prefix('panel/customer')->name('panel.customer.')->middleware(['auth', 'role:customer'])->group(function () {
    // ... existing routes ...
    
    // Payment Gateway Routes
    Route::get('/payments/gateways', [CustomerController::class, 'viewPaymentGateways'])->name('payments.gateways');
    Route::post('/payments/advance', [CustomerController::class, 'initiateAdvancePayment'])->name('payments.advance');
    Route::post('/payments/invoice/{invoice}', [CustomerController::class, 'initiateInvoicePayment'])->name('payments.invoice');
    Route::get('/payments/success', [CustomerController::class, 'paymentSuccess'])->name('payments.success');
    Route::get('/payments/failure', [CustomerController::class, 'paymentFailure'])->name('payments.failure');
    Route::get('/payments/cancel', [CustomerController::class, 'paymentCancel'])->name('payments.cancel');
});
```

### Step 2: Add Controller Methods

Add these methods to `CustomerController.php`:

```php
use App\Services\PaymentGatewayService;

/**
 * Display available payment gateways.
 */
public function viewPaymentGateways(): View
{
    $user = auth()->user();
    
    $gateways = PaymentGateway::where('tenant_id', $user->tenant_id)
        ->where('is_active', true)
        ->get();
    
    $pendingInvoices = Invoice::where('user_id', $user->id)
        ->whereIn('status', ['pending', 'overdue'])
        ->with('items')
        ->get();
    
    return view('panels.customer.payments.gateways', compact('gateways', 'pendingInvoices'));
}

/**
 * Initiate advance payment.
 */
public function initiateAdvancePayment(Request $request, PaymentGatewayService $gatewayService): RedirectResponse
{
    $request->validate([
        'amount' => 'required|numeric|min:1',
        'gateway_slug' => 'required|exists:payment_gateways,slug',
    ]);
    
    $user = auth()->user();
    
    // Create a temporary invoice for advance payment
    $invoice = Invoice::create([
        'tenant_id' => $user->tenant_id,
        'user_id' => $user->id,
        'invoice_number' => 'ADV-' . time(),
        'due_date' => now(),
        'total_amount' => $request->amount,
        'status' => 'pending',
        'type' => 'advance',
    ]);
    
    try {
        $result = $gatewayService->initiatePayment($invoice, $request->gateway_slug);
        
        return redirect($result['redirect_url']);
    } catch (\Exception $e) {
        return back()->with('error', 'Payment initiation failed: ' . $e->getMessage());
    }
}

/**
 * Initiate invoice payment.
 */
public function initiateInvoicePayment(Request $request, Invoice $invoice, PaymentGatewayService $gatewayService): RedirectResponse
{
    $request->validate([
        'gateway_slug' => 'required|exists:payment_gateways,slug',
    ]);
    
    $user = auth()->user();
    
    // Verify invoice belongs to user
    if ($invoice->user_id !== $user->id) {
        abort(403, 'Unauthorized access to invoice');
    }
    
    try {
        $result = $gatewayService->initiatePayment($invoice, $request->gateway_slug);
        
        return redirect($result['redirect_url']);
    } catch (\Exception $e) {
        return back()->with('error', 'Payment initiation failed: ' . $e->getMessage());
    }
}

/**
 * Handle successful payment.
 */
public function paymentSuccess(Request $request): View
{
    $transactionId = $request->query('transaction_id');
    
    return view('panels.customer.payments.success', compact('transactionId'));
}

/**
 * Handle failed payment.
 */
public function paymentFailure(Request $request): View
{
    $error = $request->query('error', 'Payment failed');
    
    return view('panels.customer.payments.failure', compact('error'));
}

/**
 * Handle cancelled payment.
 */
public function paymentCancel(): View
{
    return view('panels.customer.payments.cancel');
}
```

### Step 3: Create Views

#### 3.1 Payment Gateways View
Create `resources/views/panels/customer/payments/gateways.blade.php`:

```blade
@extends('panels.layouts.app')

@section('title', 'Payment Options')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Payment Options</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Choose a payment method</p>
    </div>

    <!-- Advance Payment -->
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Advance Payment</h2>
        <form method="POST" action="{{ route('panel.customer.payments.advance') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount (BDT)</label>
                    <input type="number" name="amount" min="1" step="0.01" required 
                           class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Gateway</label>
                    <select name="gateway_slug" required 
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Select Gateway</option>
                        @foreach($gateways as $gateway)
                        <option value="{{ $gateway->slug }}">{{ $gateway->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Proceed to Payment
            </button>
        </form>
    </div>

    <!-- Pending Invoices -->
    @if($pendingInvoices->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Pay Pending Invoices</h2>
        <div class="space-y-4">
            @foreach($pendingInvoices as $invoice)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">Invoice #{{ $invoice->invoice_number }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-2">{{ number_format($invoice->total_amount, 2) }} BDT</p>
                    </div>
                    <form method="POST" action="{{ route('panel.customer.payments.invoice', $invoice) }}">
                        @csrf
                        <select name="gateway_slug" required class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 mb-2">
                            <option value="">Select Gateway</option>
                            @foreach($gateways as $gateway)
                            <option value="{{ $gateway->slug }}">{{ $gateway->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded block w-full">
                            Pay Now
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
```

#### 3.2 Success Page
Create `resources/views/panels/customer/payments/success.blade.php`:

```blade
@extends('panels.layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="max-w-2xl mx-auto py-12">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Payment Successful!</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Your payment has been processed successfully.</p>
        @if($transactionId)
        <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">Transaction ID: {{ $transactionId }}</p>
        @endif
        <a href="{{ route('panel.customer.billing') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
            View Billing
        </a>
    </div>
</div>
@endsection
```

#### 3.3 Failure Page
Create `resources/views/panels/customer/payments/failure.blade.php`:

```blade
@extends('panels.layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="max-w-2xl mx-auto py-12">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
            <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Payment Failed</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $error }}</p>
        <div class="flex justify-center space-x-4">
            <a href="{{ route('panel.customer.payments.gateways') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Try Again
            </a>
            <a href="{{ route('panel.customer.dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
```

#### 3.4 Cancel Page
Create `resources/views/panels/customer/payments/cancel.blade.php`:

```blade
@extends('panels.layouts.app')

@section('title', 'Payment Cancelled')

@section('content')
<div class="max-w-2xl mx-auto py-12">
    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
            <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Payment Cancelled</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">You have cancelled the payment process.</p>
        <div class="flex justify-center space-x-4">
            <a href="{{ route('panel.customer.payments.gateways') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                Try Again
            </a>
            <a href="{{ route('panel.customer.dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
```

### Step 4: Update Billing View

Add a "Pay Online" button to the existing billing view (`resources/views/panels/customer/billing.blade.php`):

```blade
<!-- Add this link in the invoices section -->
<a href="{{ route('panel.customer.payments.gateways') }}" 
   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
    </svg>
    Pay Online
</a>
```

## Testing

### Test Scenarios

1. **Advance Payment:**
   - Go to /panel/customer/payments/gateways
   - Enter amount and select gateway
   - Verify redirect to gateway
   - Complete payment
   - Verify success page

2. **Invoice Payment:**
   - View pending invoices
   - Select gateway for an invoice
   - Complete payment
   - Verify invoice status updated

3. **Gateway Selection:**
   - Verify only active gateways appear
   - Test each gateway type
   - Verify test/production mode

4. **Error Handling:**
   - Test with invalid amount
   - Test with disabled gateway
   - Test payment cancellation
   - Test payment failure

## Security Considerations

1. **Webhook Validation:** Ensure webhook endpoints validate signatures
2. **Transaction Verification:** Always verify payment status with gateway
3. **Idempotency:** Handle duplicate payment attempts
4. **Logging:** Log all payment attempts and responses
5. **Balance Updates:** Use database transactions for balance updates

## Configuration

Each gateway requires specific configuration in the `payment_gateways` table:

### bKash
```json
{
    "app_key": "your_app_key",
    "app_secret": "your_app_secret",
    "username": "your_username",
    "password": "your_password"
}
```

### Nagad
```json
{
    "merchant_id": "your_merchant_id",
    "merchant_number": "your_merchant_number",
    "public_key": "your_public_key",
    "private_key": "your_private_key"
}
```

### SSLCommerz
```json
{
    "store_id": "your_store_id",
    "store_password": "your_store_password"
}
```

### Stripe
```json
{
    "publishable_key": "pk_test_...",
    "secret_key": "sk_test_..."
}
```

## Troubleshooting

### Common Issues

1. **Redirect URL not working:** Check route names and middleware
2. **Gateway not appearing:** Verify `is_active` flag and tenant_id
3. **Webhook failures:** Check IP whitelist and signature validation
4. **Balance not updating:** Review transaction handling

### Debug Mode

Enable payment gateway debug logging:

```php
// In config/logging.php
'channels' => [
    'payment' => [
        'driver' => 'single',
        'path' => storage_path('logs/payment.log'),
        'level' => 'debug',
    ],
],
```

## Support

For gateway-specific issues, refer to:
- bKash: https://developer.bkash.com/
- Nagad: https://nagad.com.bd/merchant/
- SSLCommerz: https://developer.sslcommerz.com/
- Stripe: https://stripe.com/docs

---

**Document Version:** 1.0.0  
**Last Updated:** January 26, 2026
