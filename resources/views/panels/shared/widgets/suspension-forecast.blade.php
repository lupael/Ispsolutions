{{-- Suspension Forecast Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border-2 border-yellow-500" id="suspension-forecast-widget">
    <div class="px-6 py-4 bg-yellow-50 dark:bg-yellow-900/20 flex justify-between items-center">
        <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Today's Suspension Forecast
        </h5>
        <button class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition" onclick="refreshWidget('suspension_forecast')" title="Refresh">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>
    <div class="p-6">
        @if(isset($widgets['suspension_forecast']))
            @php $data = $widgets['suspension_forecast']; @endphp
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $data['total_count'] }}</div>
                    <small class="text-sm text-gray-500 dark:text-gray-400">Customers at Risk</small>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format($data['total_amount'], 2) }}</div>
                    <small class="text-sm text-gray-500 dark:text-gray-400">Total Risk Amount (BDT)</small>
                </div>
            </div>

            @if(!empty($data['by_package']))
                <div class="mb-4">
                    <h6 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">By Package</h6>
                    <div class="space-y-2">
                        @foreach($data['by_package'] as $package)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $package['package_name'] }}</span>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">{{ $package['count'] }}</span>
                                    <small class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($package['amount'], 2) }} BDT</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($data['by_zone']))
                <div class="mb-4">
                    <h6 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">By Zone</h6>
                    <div class="space-y-2">
                        @foreach($data['by_zone'] as $zone)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm text-gray-900 dark:text-gray-100">Zone {{ $zone['zone_id'] }}</span>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $zone['count'] }}</span>
                                    <small class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($zone['amount'], 2) }} BDT</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

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
                <p>No suspension forecast data available</p>
            </div>
        @endif
    </div>
</div>
