@props(['profile'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <!-- Profile Header -->
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $profile->name }}</h3>
            @if($profile->description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $profile->description }}</p>
            @endif
        </div>
        <span class="px-2 py-1 text-xs rounded-full 
            @if($profile->type === 'daily') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
            @elseif($profile->type === 'monthly') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
            @endif">
            {{ ucfirst($profile->type) }}
        </span>
    </div>

    <!-- Billing Schedule -->
    <div class="space-y-3">
        <div class="flex items-center text-sm">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-gray-700 dark:text-gray-300">
                <span class="font-medium">Due Date:</span>
                @if($profile->type === 'monthly')
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $profile->due_date_figure }}</span>
                @else
                    {{ $profile->schedule_description }}
                @endif
            </span>
        </div>

        @if($profile->grace_period_days > 0)
            <div class="flex items-center text-sm">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gray-700 dark:text-gray-300">
                    <span class="font-medium">Grace Period:</span>
                    <span class="text-orange-600 dark:text-orange-400">{{ $profile->gracePeriod() }} days</span>
                </span>
            </div>
        @endif

        @if($profile->auto_suspend)
            <div class="flex items-center text-sm">
                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-amber-600 dark:text-amber-400 text-sm">Auto-suspend enabled</span>
            </div>
        @endif

        <!-- Customer Count -->
        <div class="flex items-center text-sm pt-2 border-t border-gray-200 dark:border-gray-700">
            <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-gray-700 dark:text-gray-300">
                <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $profile->users_count ?? 0 }}</span>
                {{ Str::plural('customer', $profile->users_count ?? 0) }} assigned
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-2">
        <a href="{{ route('panel.admin.billing-profiles.show', $profile) }}" 
           class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
            View Details
        </a>
        <a href="{{ route('panel.admin.billing-profiles.edit', $profile) }}" 
           class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
            Edit
        </a>
    </div>
</div>
