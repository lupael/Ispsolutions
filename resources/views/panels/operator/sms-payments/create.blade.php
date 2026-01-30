@extends('panels.layouts.app')

@section('title', 'Purchase SMS Credits')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Purchase SMS Credits</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Buy SMS credits to send messages to your customers</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Current Balance</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format(auth()->user()->sms_balance ?? 0) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">SMS Credits</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Purchase Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Select SMS Package</h2>
                    
                    <form method="POST" id="smsPaymentForm" class="space-y-6">
                        @csrf

                        <!-- Package Selection (Radio Buttons) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="package-selection-grid">
                            <!-- Package 1: 1000 SMS -->
                            <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('package') == 'pkg_1000' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="radio" name="package" value="pkg_1000" data-quantity="1000" data-amount="500" class="sr-only package-radio" {{ old('package') == 'pkg_1000' ? 'checked' : '' }} required>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">1,000 SMS</span>
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">৳500</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">৳0.50 per SMS</p>
                                <div class="absolute top-4 right-4">
                                    <svg class="h-5 w-5 text-blue-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>

                            <!-- Package 2: 5000 SMS -->
                            <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('package') == 'pkg_5000' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="radio" name="package" value="pkg_5000" data-quantity="5000" data-amount="2250" class="sr-only package-radio" {{ old('package') == 'pkg_5000' ? 'checked' : '' }}>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">5,000 SMS</span>
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">৳2,250</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">৳0.45 per SMS</p>
                                <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-200 rounded">Save 10%</span>
                                <div class="absolute top-4 right-4">
                                    <svg class="h-5 w-5 text-blue-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>

                            <!-- Package 3: 10000 SMS -->
                            <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('package') == 'pkg_10000' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="radio" name="package" value="pkg_10000" data-quantity="10000" data-amount="4000" class="sr-only package-radio" {{ old('package') == 'pkg_10000' ? 'checked' : '' }}>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">10,000 SMS</span>
                                    <span class="text-sm font-medium text-blue-600 dark:text-blue-400">৳4,000</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">৳0.40 per SMS</p>
                                <span class="inline-block mt-1 px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 dark:bg-green-900 dark:text-green-200 rounded">Save 20%</span>
                                <div class="absolute top-4 right-4">
                                    <svg class="h-5 w-5 text-blue-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>

                            <!-- Package 4: Custom -->
                            <label class="relative flex flex-col p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('package') == 'custom' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                <input type="radio" name="package" value="custom" data-quantity="0" data-amount="0" class="sr-only package-radio" {{ old('package') == 'custom' ? 'checked' : '' }}>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">Custom Amount</span>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Variable</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Enter your own quantity</p>
                                <div class="absolute top-4 right-4">
                                    <svg class="h-5 w-5 text-blue-600 hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>
                        </div>

                        <!-- Custom Quantity Input (shown when custom is selected) -->
                        <div id="customQuantitySection" class="hidden">
                            <label for="custom_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SMS Quantity (Min: 100, Max: 100,000)</label>
                            <input type="number" name="custom_quantity" id="custom_quantity" min="100" max="100000" step="100" placeholder="Enter quantity (e.g., 2500)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rate: ৳0.50 per SMS (no discount for custom amounts)</p>
                        </div>

                        <!-- Hidden inputs for actual submission -->
                        <input type="hidden" name="sms_quantity" id="sms_quantity" value="{{ old('sms_quantity') }}">
                        <input type="hidden" name="amount" id="amount" value="{{ old('amount') }}">

                        <!-- Payment Method Selection -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Payment Method</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('payment_method') == 'bkash' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="radio" name="payment_method" value="bkash" class="sr-only" {{ old('payment_method') == 'bkash' ? 'checked' : '' }} required>
                                    <div class="flex items-center space-x-3">
                                        <img src="/images/payment/bkash.png" alt="bKash" class="h-8 w-8 object-contain" onerror="this.style.display='none'">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">bKash</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('payment_method') == 'nagad' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="radio" name="payment_method" value="nagad" class="sr-only" {{ old('payment_method') == 'nagad' ? 'checked' : '' }}>
                                    <div class="flex items-center space-x-3">
                                        <img src="/images/payment/nagad.png" alt="Nagad" class="h-8 w-8 object-contain" onerror="this.style.display='none'">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">Nagad</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('payment_method') == 'rocket' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="radio" name="payment_method" value="rocket" class="sr-only" {{ old('payment_method') == 'rocket' ? 'checked' : '' }}>
                                    <div class="flex items-center space-x-3">
                                        <img src="/images/payment/rocket.png" alt="Rocket" class="h-8 w-8 object-contain" onerror="this.style.display='none'">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">Rocket</span>
                                    </div>
                                </label>
                                <label class="relative flex items-center p-3 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors {{ old('payment_method') == 'sslcommerz' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                                    <input type="radio" name="payment_method" value="sslcommerz" class="sr-only" {{ old('payment_method') == 'sslcommerz' ? 'checked' : '' }}>
                                    <div class="flex items-center space-x-3">
                                        <img src="/images/payment/sslcommerz.png" alt="SSLCommerz" class="h-8 w-8 object-contain" onerror="this.style.display='none'">
                                        <span class="font-medium text-gray-900 dark:text-gray-100">SSLCommerz</span>
                                    </div>
                                </label>
                            </div>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('panel.operator.dashboard') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Cancel
                            </a>
                            <button type="submit" id="submitBtn" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Proceed to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">SMS Quantity:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100" id="summary_quantity">-</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Rate per SMS:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100" id="summary_rate">-</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total Amount:</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400" id="summary_total">৳0</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-4">
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">After Purchase</h3>
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p>New Balance:</p>
                            <p class="text-2xl font-bold mt-1" id="summary_new_balance">{{ number_format(auth()->user()->sms_balance ?? 0) }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">SMS Credits</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Important Notes
                        </h3>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• SMS credits don't expire</li>
                            <li>• Minimum purchase: 100 SMS</li>
                            <li>• Instant credit after payment</li>
                            <li>• Secure payment gateway</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('smsPaymentForm');
    const packageRadios = document.querySelectorAll('.package-radio');
    const customSection = document.getElementById('customQuantitySection');
    const customInput = document.getElementById('custom_quantity');
    const submitBtn = document.getElementById('submitBtn');
    const currentBalance = {{ auth()->user()->sms_balance ?? 0 }};

    // Update summary when package selection changes
    packageRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            updatePackageSelection(this);
            updateSummary();
        });
    });

    // Update summary when custom quantity changes
    customInput.addEventListener('input', function() {
        updateSummary();
    });

    function updatePackageSelection(radio) {
        // Update visual selection - scope to only package labels
        const packageGrid = document.getElementById('package-selection-grid');
        packageGrid.querySelectorAll('label').forEach(label => {
            label.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            label.classList.add('border-gray-300', 'dark:border-gray-600');
            label.querySelector('.check-icon')?.classList.add('hidden');
        });
        
        radio.closest('label').classList.remove('border-gray-300', 'dark:border-gray-600');
        radio.closest('label').classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        radio.closest('label').querySelector('.check-icon')?.classList.remove('hidden');

        // Show/hide custom input
        if (radio.value === 'custom') {
            customSection.classList.remove('hidden');
            customInput.required = true;
        } else {
            customSection.classList.add('hidden');
            customInput.required = false;
            customInput.value = '';
        }
    }

    function updateSummary() {
        const selectedPackage = document.querySelector('.package-radio:checked');
        if (!selectedPackage) return;

        let quantity, amount, rate;

        if (selectedPackage.value === 'custom') {
            quantity = parseInt(customInput.value) || 0;
            rate = 0.50; // Base rate for custom
            amount = quantity * rate;
        } else {
            quantity = parseInt(selectedPackage.dataset.quantity);
            amount = parseFloat(selectedPackage.dataset.amount);
            rate = quantity > 0 ? amount / quantity : 0;
        }

        // Update hidden form fields
        document.getElementById('sms_quantity').value = quantity;
        document.getElementById('amount').value = amount;

        // Update summary display
        document.getElementById('summary_quantity').textContent = quantity > 0 ? quantity.toLocaleString() : '-';
        document.getElementById('summary_rate').textContent = rate > 0 ? `৳${rate.toFixed(2)}` : '-';
        document.getElementById('summary_total').textContent = amount > 0 ? `৳${amount.toLocaleString()}` : '৳0';
        document.getElementById('summary_new_balance').textContent = (currentBalance + quantity).toLocaleString();

        // Enable/disable submit button - check both conditions
        const paymentMethodSelected = document.querySelector('input[name="payment_method"]:checked');
        submitBtn.disabled = quantity < 100 || !paymentMethodSelected;
    }

    // Update submit state when payment method is selected
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', () => {
            updateSummary();
        });
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validate
        if (!data.sms_quantity || data.sms_quantity < 100) {
            alert('Please select a valid SMS package (minimum 100 SMS)');
            return;
        }

        if (!data.payment_method) {
            alert('Please select a payment method');
            return;
        }

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

        try {
            const response = await fetch('/api/sms-payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data),
                credentials: 'same-origin'
            });

            const result = await response.json();

            if (result.success) {
                // TODO: Redirect to payment gateway
                alert('Payment initiated! Redirecting to payment gateway...');
                // window.location.href = result.data.payment_url;
                
                // For now, just reload to show success
                window.location.href = '{{ route("panel.operator.sms-payments.index") }}';
            } else {
                alert('Error: ' + (result.message || 'Payment initiation failed'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>Proceed to Payment';
            }
        } catch (error) {
            console.error('Payment error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>Proceed to Payment';
        }
    });

    // Initialize
    const checkedRadio = document.querySelector('.package-radio:checked');
    if (checkedRadio) {
        updatePackageSelection(checkedRadio);
        updateSummary();
    }
});
</script>
@endpush
@endsection
