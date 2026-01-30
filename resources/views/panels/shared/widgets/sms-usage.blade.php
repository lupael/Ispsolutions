{{-- SMS Usage Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border-2 border-blue-500" id="sms-usage-widget">
    <div class="px-6 py-4 bg-blue-50 dark:bg-blue-900/20 flex justify-between items-center">
        <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
            </svg>
            SMS Usage Today
        </h5>
        <button class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition" onclick="refreshWidget('sms_usage')" title="Refresh">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>
    <div class="p-6">
        @if(isset($widgets['sms_usage']))
            @php $data = $widgets['sms_usage']; @endphp
            
            <div class="text-center mb-4">
                <div class="text-4xl font-bold text-blue-500 dark:text-blue-400">{{ $data['total_sent'] }}</div>
                <small class="text-sm text-gray-500 dark:text-gray-400">Total SMS Sent</small>
            </div>

            <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2 text-center">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $data['sent_count'] }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Delivered</small>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-2 text-center">
                    <div class="text-lg font-bold text-red-600 dark:text-red-400">{{ $data['failed_count'] }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Failed</small>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-2 text-center">
                    <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ $data['pending_count'] }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Pending</small>
                </div>
            </div>

            <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-3 mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Cost Today</span>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ number_format($data['total_cost'], 4) }} BDT</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Used Balance</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($data['used_balance'], 4) }} BDT</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Remaining Balance</span>
                    @if($data['balance_tracking_available'] ?? false)
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ number_format($data['remaining_balance'], 2) }} BDT</span>
                    @else
                        <span class="text-sm italic text-gray-500 dark:text-gray-400">Not available</span>
                    @endif
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                <small class="text-sm text-blue-900 dark:text-blue-100 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Success Rate: 
                    @php
                        $successRate = $data['total_sent'] > 0 
                            ? round(($data['sent_count'] / $data['total_sent']) * 100, 1) 
                            : 0;
                    @endphp
                    <strong class="ml-1">{{ $successRate }}%</strong>
                </small>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <small class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Data for: {{ $data['date'] }}
                </small>
            </div>
        @else
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <p>No SMS usage data available</p>
            </div>
        @endif
    </div>
</div>
