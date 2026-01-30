{{-- SMS Balance Widget - Displays SMS credits and purchase options --}}
{{-- Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration --}}

<div id="sms-balance-widget" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                <i class="fas fa-sms text-blue-500 mr-2"></i>
                SMS Balance
            </h3>
            <button 
                onclick="refreshSmsBalance()" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                title="Refresh balance"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        @if(isset($sms_balance))
            {{-- Current Balance Display --}}
            <div class="text-center mb-6">
                <div class="text-4xl font-bold {{ $sms_balance['is_low_balance'] ? 'text-red-600' : 'text-blue-600' }} dark:{{ $sms_balance['is_low_balance'] ? 'text-red-400' : 'text-blue-400' }}">
                    {{ number_format($sms_balance['current_balance']) }}
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Available SMS Credits</p>
            </div>

            {{-- Low Balance Warning --}}
            @if($sms_balance['is_low_balance'])
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Low SMS Balance</h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                                Your balance is below {{ number_format($sms_balance['low_balance_threshold']) }} credits. 
                                <a href="{{ route('panel.operator.sms-payments.create') }}" class="font-semibold underline hover:text-red-600">
                                    Purchase more credits
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Monthly Statistics --}}
            @if(isset($sms_balance['monthly_stats']))
                @php $stats = $sms_balance['monthly_stats']; @endphp
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">This Month</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mt-1">
                            {{ number_format($stats['total_used']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">SMS Used</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400 uppercase tracking-wide">Transactions</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mt-1">
                            {{ number_format($stats['transaction_count']) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This Month</p>
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="space-y-2">
                <a 
                    href="{{ route('panel.operator.sms-payments.create') }}" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buy SMS Credits
                </a>
                <a 
                    href="{{ route('panel.operator.sms-payments.index') }}" 
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    View History
                </a>
            </div>

            {{-- Quick Info --}}
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    SMS credits are deducted when sending messages to customers
                </p>
            </div>
        @else
            {{-- Loading or Error State --}}
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Unable to load SMS balance</p>
                <button 
                    onclick="refreshSmsBalance()" 
                    class="mt-3 text-sm text-blue-600 hover:text-blue-500"
                >
                    Try again
                </button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
/**
 * Refresh SMS balance widget data via AJAX
 */
function refreshSmsBalance() {
    // Show loading state - use specific widget ID
    const widget = document.getElementById('sms-balance-widget');
    if (widget) {
        widget.style.opacity = '0.6';
    }

    // Fetch updated balance from API (uses session authentication)
    fetch('/api/sms-payments/balance', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to update the widget with fresh data
            window.location.reload();
        } else {
            console.error('Failed to refresh SMS balance:', data.message);
            alert('Failed to refresh SMS balance. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error refreshing SMS balance:', error);
        alert('Error refreshing SMS balance. Please try again.');
    })
    .finally(() => {
        if (widget) {
            widget.style.opacity = '1';
        }
    });
}
</script>
@endpush
