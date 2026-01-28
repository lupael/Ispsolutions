@props(['expiringCustomers', 'days' => 7])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Expiring in {{ $days }} Days
        </h3>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
            {{ $expiringCustomers->count() }}
        </span>
    </div>
    
    @if($expiringCustomers->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No customers expiring soon</p>
        </div>
    @else
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($expiringCustomers as $customer)
                @php
                    use Carbon\Carbon;
                    $timezone = $customer->billingProfile?->timezone ?? config('app.timezone', 'UTC');
                    $now = Carbon::now($timezone);
                    $expiryDate = Carbon::parse($customer->expiry_date)->timezone($timezone);
                    $daysRemaining = $now->diffInDays($expiryDate, false);
                    
                    if ($daysRemaining < 0) {
                        $urgencyColor = 'red';
                        $urgencyText = 'Expired';
                    } elseif ($expiryDate->isToday()) {
                        $urgencyColor = 'red';
                        $urgencyText = 'Today';
                    } elseif ($daysRemaining <= 1) {
                        $urgencyColor = 'red';
                        $urgencyText = '1 day';
                    } elseif ($daysRemaining <= 3) {
                        $urgencyColor = 'orange';
                        $urgencyText = $daysRemaining . ' days';
                    } else {
                        $urgencyColor = 'yellow';
                        $urgencyText = $daysRemaining . ' days';
                    }
                    
                    $colorClasses = [
                        'red' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                        'orange' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
                        'yellow' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                    ];
                    
                    $badgeClasses = [
                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    ];
                @endphp
                
                <div class="border {{ $colorClasses[$urgencyColor] }} rounded-lg p-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('panel.admin.customers.show', $customer) }}" 
                               class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $customer->name }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $customer->username ?? $customer->email }}
                            </p>
                            @if($customer->service_package_id && $customer->package)
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                                    <span class="font-medium">Package:</span> {{ $customer->package->name }}
                                </p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end ml-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $badgeClasses[$urgencyColor] }}">
                                {{ $urgencyText }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $expiryDate->format('M d') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex space-x-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('panel.admin.customers.show', $customer) }}" 
                           class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                            View
                        </a>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <a href="{{ route('panel.admin.customers.edit', $customer) }}" 
                           class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                            Extend
                        </a>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button onclick="alert('Email reminder feature coming soon')" 
                                class="text-xs text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 font-medium">
                            Remind
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($expiringCustomers->count() > 5)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                <a href="{{ route('panel.admin.customers.index', ['expiring_days' => $days]) }}" 
                   class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                    View All {{ $expiringCustomers->count() }} Expiring Customers →
                </a>
            </div>
        @endif
    @endif
</div>
