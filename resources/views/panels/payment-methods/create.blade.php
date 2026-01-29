@extends('panels.layouts.app')

@section('title', 'Add Payment Method')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add Payment Method</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Set up bKash for quick and secure payments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">How It Works</h2>
            
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900">
                            <span class="text-blue-600 dark:text-blue-300 font-bold">1</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Enter Your bKash Number</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Provide your bKash mobile number to get started</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900">
                            <span class="text-blue-600 dark:text-blue-300 font-bold">2</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Authorize with bKash</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">You'll be redirected to bKash to approve the connection</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900">
                            <span class="text-blue-600 dark:text-blue-300 font-bold">3</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">Start Using</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Make quick payments without entering details each time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agreement Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Enter bKash Details</h2>
            
            <form id="agreementForm" class="space-y-6">
                @csrf

                <!-- Mobile Number -->
                <div>
                    <label for="mobile_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">bKash Mobile Number *</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">+88</span>
                        </div>
                        <input type="text" 
                               name="mobile_number" 
                               id="mobile_number" 
                               placeholder="01XXXXXXXXX"
                               pattern="01[3-9][0-9]{8}"
                               maxlength="11"
                               required
                               class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your 11-digit bKash mobile number (e.g., 01XXXXXXXXX)</p>
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" 
                               name="terms" 
                               type="checkbox" 
                               required
                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="font-medium text-gray-700 dark:text-gray-300">
                            I agree to the terms and conditions *
                        </label>
                        <p class="text-gray-500 dark:text-gray-400">
                            By checking this box, you authorize us to charge your bKash account for payments on your behalf. You can revoke this authorization at any time.
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('panel.bkash-agreements.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    <button type="submit" id="submitBtn" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Continue to bKash
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Notice -->
    <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Secure & Safe</h3>
                <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                    <p>Your payment information is encrypted and securely stored by bKash. We never have access to your bKash PIN or password.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('agreementForm');
    const submitBtn = document.getElementById('submitBtn');
    const mobileInput = document.getElementById('mobile_number');

    // Format mobile number input
    mobileInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        e.target.value = value;
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Validate mobile number format
        const mobileRegex = /^01[3-9]\d{8}$/;
        if (!mobileRegex.test(data.mobile_number)) {
            alert('Please enter a valid bKash mobile number (e.g., 01712345678)');
            return;
        }

        if (!data.terms) {
            alert('Please accept the terms and conditions');
            return;
        }

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

        try {
            const response = await fetch('/api/bkash-agreements', {
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

            if (result.success && result.data.redirect_url) {
                // Redirect to bKash for authorization
                window.location.href = result.data.redirect_url;
            } else {
                alert('Error: ' + (result.message || 'Failed to create agreement'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Continue to bKash';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Continue to bKash';
        }
    });
});
</script>
@endpush
@endsection
