@props(['customer', 'limit' => 10])

@php
    $activityService = app(\App\Services\CustomerActivityService::class);
    $activities = $activityService->getActivityTimeline($customer, $limit);
    $stats = $activityService->getActivityStats($customer, 30);
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('customers.activity_feed') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('customers.activity_help') }}
                </p>
            </div>
        </div>

        <!-- Activity Statistics (Last 30 days) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ $stats['payments_count'] }}
                </div>
                <div class="text-sm text-green-700 dark:text-green-300">
                    {{ __('customers.total_payments') }}
                </div>
                <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                    ${{ number_format($stats['payments_total'], 2) }}
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $stats['package_changes'] }}
                </div>
                <div class="text-sm text-blue-700 dark:text-blue-300">
                    {{ __('customers.package_changes') }}
                </div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $stats['status_changes'] }}
                </div>
                <div class="text-sm text-purple-700 dark:text-purple-300">
                    {{ __('Status Changes') }}
                </div>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ $stats['tickets_count'] }}
                </div>
                <div class="text-sm text-red-700 dark:text-red-300">
                    {{ __('customers.tickets_opened') }}
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        @if($activities->count() > 0)
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            @php
                                // Map colors to full Tailwind classes for proper compilation
                                $bgClass = match($activity['color']) {
                                    'green' => 'bg-green-100 dark:bg-green-900/30',
                                    'blue' => 'bg-blue-100 dark:bg-blue-900/30',
                                    'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                    'red' => 'bg-red-100 dark:bg-red-900/30',
                                    'gray' => 'bg-gray-100 dark:bg-gray-900/30',
                                    default => 'bg-gray-100 dark:bg-gray-900/30',
                                };
                                $iconClass = match($activity['color']) {
                                    'green' => 'text-green-600 dark:text-green-400',
                                    'blue' => 'text-blue-600 dark:text-blue-400',
                                    'yellow' => 'text-yellow-600 dark:text-yellow-400',
                                    'red' => 'text-red-600 dark:text-red-400',
                                    'gray' => 'text-gray-600 dark:text-gray-400',
                                    default => 'text-gray-600 dark:text-gray-400',
                                };
                            @endphp
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $bgClass }}">
                                @if($activity['icon'] === 'currency-dollar')
                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif($activity['icon'] === 'refresh')
                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                @elseif($activity['icon'] === 'shield-check')
                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                @elseif($activity['icon'] === 'ticket')
                                    <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                @endif
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $activity['title'] }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $activity['description'] }}
                                    </p>
                                </div>
                                <time class="text-xs text-gray-500 dark:text-gray-500 whitespace-nowrap">
                                    {{ $activity['timestamp']->diffForHumans() }}
                                </time>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($activities->count() >= $limit)
                <div class="mt-6 text-center">
                    <a href="{{ route('audit-logs.index', ['auditable_type' => 'User', 'auditable_id' => $customer->id]) }}" 
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                        {{ __('customers.view_all_activity') }} →
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ __('customers.no_activity') }}
                </h3>
            </div>
        @endif
    </div>
</div>
