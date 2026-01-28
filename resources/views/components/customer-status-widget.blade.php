@props(['statusDistribution'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Customer Status Distribution</h3>
    
    <div class="grid grid-cols-2 gap-4">
        @foreach($statusDistribution as $status => $count)
            @php
                $colors = [
                    'prepaid_active' => ['bg' => 'bg-green-100 dark:bg-green-900/20', 'text' => 'text-green-700 dark:text-green-300', 'border' => 'border-green-500'],
                    'postpaid_active' => ['bg' => 'bg-blue-100 dark:bg-blue-900/20', 'text' => 'text-blue-700 dark:text-blue-300', 'border' => 'border-blue-500'],
                    'prepaid_suspended' => ['bg' => 'bg-orange-100 dark:bg-orange-900/20', 'text' => 'text-orange-700 dark:text-orange-300', 'border' => 'border-orange-500'],
                    'postpaid_suspended' => ['bg' => 'bg-orange-100 dark:bg-orange-900/20', 'text' => 'text-orange-700 dark:text-orange-300', 'border' => 'border-orange-500'],
                    'prepaid_expired' => ['bg' => 'bg-red-100 dark:bg-red-900/20', 'text' => 'text-red-700 dark:text-red-300', 'border' => 'border-red-500'],
                    'postpaid_expired' => ['bg' => 'bg-red-100 dark:bg-red-900/20', 'text' => 'text-red-700 dark:text-red-300', 'border' => 'border-red-500'],
                    'prepaid_inactive' => ['bg' => 'bg-gray-100 dark:bg-gray-700/20', 'text' => 'text-gray-700 dark:text-gray-300', 'border' => 'border-gray-500'],
                    'postpaid_inactive' => ['bg' => 'bg-gray-100 dark:bg-gray-700/20', 'text' => 'text-gray-700 dark:text-gray-300', 'border' => 'border-gray-500'],
                ];
                
                $labels = [
                    'prepaid_active' => 'Prepaid Active',
                    'postpaid_active' => 'Postpaid Active',
                    'prepaid_suspended' => 'Prepaid Suspended',
                    'postpaid_suspended' => 'Postpaid Suspended',
                    'prepaid_expired' => 'Prepaid Expired',
                    'postpaid_expired' => 'Postpaid Expired',
                    'prepaid_inactive' => 'Prepaid Inactive',
                    'postpaid_inactive' => 'Postpaid Inactive',
                ];
                
                $color = $colors[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-500'];
                $label = $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
            @endphp
            
            <a href="{{ route('panel.admin.customers.index', ['status' => $status]) }}" 
               class="block {{ $color['bg'] }} rounded-lg p-4 border-l-4 {{ $color['border'] }} hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium {{ $color['text'] }} mb-1">{{ $label }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $count }}</p>
                    </div>
                    <div class="text-right">
                        <svg class="w-8 h-8 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    
    @if($statusDistribution->isEmpty())
        <p class="text-center text-gray-500 dark:text-gray-400 py-8">No customer data available</p>
    @endif
</div>
