@extends('panels.layouts.app')

@section('title', 'Saved Payment Methods')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Saved Payment Methods</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your saved payment methods for quick checkouts</p>
                </div>
                <div>
                    <a href="{{ route('panel.bkash-agreements.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Payment Method
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($agreements->isEmpty())
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">No saved payment methods</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Get started by adding your first payment method for quick and easy payments.</p>
                <div class="mt-6">
                    <a href="{{ route('panel.bkash-agreements.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Add Payment Method
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Payment Methods List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($agreements as $agreement)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Status Badge -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <img src="/images/payment/bkash.png" alt="bKash" class="h-8 w-auto" onerror="this.style.display='none'">
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">bKash</span>
                            </div>
                            @if($agreement->isActive())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <svg class="mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Active
                                </span>
                            @elseif($agreement->isPending())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Pending
                                </span>
                            @elseif($agreement->isCancelled())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Expired
                                </span>
                            @endif
                        </div>

                        <!-- Mobile Number -->
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Mobile Number</p>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $agreement->customer_msisdn }}</p>
                        </div>

                        <!-- Agreement Info -->
                        <div class="mb-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Agreement ID:</span>
                                <span class="font-mono text-gray-900 dark:text-gray-100">{{ substr($agreement->agreement_id, 0, 12) }}...</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Created:</span>
                                <span class="text-gray-900 dark:text-gray-100">{{ $agreement->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($agreement->tokens->isNotEmpty())
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Tokens:</span>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $agreement->tokens->count() }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        @if($agreement->isActive())
                            <div class="flex space-x-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <button type="button" onclick="makePayment('{{ $agreement->id }}')" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Pay Now
                                </button>
                                <button type="button" onclick="removePaymentMethod('{{ $agreement->id }}')" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 dark:bg-gray-700 dark:text-red-400 dark:border-red-600 dark:hover:bg-gray-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($agreements->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 sm:px-6 rounded-lg">
                {{ $agreements->links() }}
            </div>
        @endif
    @endif

    <!-- Info Box -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">About Saved Payment Methods</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Your payment information is securely stored with bKash</li>
                        <li>Make payments without entering your details each time</li>
                        <li>You can remove payment methods at any time</li>
                        <li>No charges are made without your explicit approval</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function makePayment(agreementId) {
    // TODO: Implement one-click payment using this agreement
    alert('One-click payment feature will be implemented here using agreement ID: ' + agreementId);
    
    // Example implementation:
    // 1. Show payment amount modal
    // 2. Call API to create payment with agreement_id
    // 3. Process payment via Bkash tokenization
    // 4. Show success/failure message
}

async function removePaymentMethod(agreementId) {
    if (!confirm('Are you sure you want to remove this payment method?')) {
        return;
    }

    try {
        const response = await fetch(`/api/bkash-agreements/${agreementId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
            alert('Payment method removed successfully');
            window.location.reload();
        } else {
            alert('Error: ' + (result.message || 'Failed to remove payment method'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}
</script>
@endpush
@endsection
