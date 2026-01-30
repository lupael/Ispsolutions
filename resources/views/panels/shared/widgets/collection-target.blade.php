{{-- Collection Target Widget --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border-2 border-green-500" id="collection-target-widget">
    <div class="px-6 py-4 bg-green-50 dark:bg-green-900/20 flex justify-between items-center">
        <h5 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
            Collection Target
        </h5>
        <button class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition" onclick="refreshWidget('collection_target')" title="Refresh">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>
    <div class="p-6">
        @if(isset($widgets['collection_target']))
            @php $data = $widgets['collection_target']; @endphp
            
            <div class="mb-3">
                <div class="flex justify-content-between mb-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Target: {{ number_format($data['target_amount'], 2) }} BDT</span>
                    <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ $data['percentage_collected'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-6 overflow-hidden">
                    <div class="bg-green-600 h-6 flex items-center justify-center text-white text-xs font-medium" 
                         style="width: {{ $data['percentage_collected'] }}%;">
                        {{ number_format($data['collected_amount'], 2) }} BDT
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ number_format($data['collected_amount'], 2) }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Collected (BDT)</small>
                </div>
                <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-3 text-center">
                    <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($data['pending_amount'], 2) }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Pending (BDT)</small>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $data['total_bills'] }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Total Bills</small>
                </div>
                <div class="text-center border-l border-r border-gray-200 dark:border-gray-700">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $data['paid_bills'] }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Paid</small>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $data['pending_bills'] }}</div>
                    <small class="text-xs text-gray-500 dark:text-gray-400">Pending</small>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                <small class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Bills due: {{ $data['date'] }}
                </small>
            </div>
        @else
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <p>No collection target data available</p>
            </div>
        @endif
    </div>
</div>
