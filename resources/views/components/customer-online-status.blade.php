@props(['customer', 'showDetails' => false])

@php
    // Check if customer has online status
    $isOnline = $customer->online_status ?? false;
    $currentSession = $isOnline ? $customer->getCurrentSession() : null;
    $sessionDuration = $currentSession ? $customer->getSessionDuration() : 0;
@endphp

<div class="inline-flex items-center space-x-2">
    <!-- Status Indicator -->
    <div class="relative">
        <span class="flex h-3 w-3">
            @if($isOnline)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            @else
                <span class="relative inline-flex rounded-full h-3 w-3 bg-gray-400 dark:bg-gray-600"></span>
            @endif
        </span>
    </div>

    <!-- Status Text -->
    <span class="text-sm font-medium {{ $isOnline ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
        {{ $isOnline ? __('customers.online') : __('customers.offline') }}
    </span>

    @if($showDetails)
        <!-- Additional Details -->
        <span class="text-xs text-gray-500 dark:text-gray-400">
            @if($isOnline && $currentSession)
                <!-- Show session duration -->
                <span class="ml-2">
                    ({{ gmdate('H:i:s', $sessionDuration) }})
                </span>
            @elseif(!$isOnline && $customer->last_seen_at)
                <!-- Show last seen -->
                <span class="ml-2">
                    {{ __('customers.last_seen') }}: {{ $customer->last_seen_at->diffForHumans() }}
                </span>
            @endif
        </span>
    @endif
</div>

@if($showDetails && $isOnline && $currentSession)
    <!-- Session Details Tooltip/Popover -->
    <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
        <div class="grid grid-cols-2 gap-2">
            @if(isset($currentSession->nas_ip_address))
                <div>
                    <span class="font-medium">{{ __('NAS IP') }}:</span>
                    {{ $currentSession->nas_ip_address }}
                </div>
            @endif
            @if(isset($currentSession->framed_ip_address))
                <div>
                    <span class="font-medium">{{ __('IP Address') }}:</span>
                    {{ $currentSession->framed_ip_address }}
                </div>
            @endif
            @if(isset($currentSession->acct_start_time))
                <div>
                    <span class="font-medium">{{ __('Connected At') }}:</span>
                    {{ \Carbon\Carbon::parse($currentSession->acct_start_time)->format('M d, H:i') }}
                </div>
            @endif
            <div>
                <span class="font-medium">{{ __('customers.session_duration') }}:</span>
                {{ gmdate('H:i:s', $sessionDuration) }}
            </div>
        </div>
    </div>
@endif
