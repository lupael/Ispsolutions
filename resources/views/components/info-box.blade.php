@props([
    'title' => 'Stat',
    'value' => '0',
    'icon' => 'chart',
    'color' => 'indigo',
    'link' => null,
    'subtitle' => null,
    'trend' => null,
    'trendValue' => null,
])

@php
$colorClasses = [
    'indigo' => 'bg-indigo-500',
    'green' => 'bg-green-500',
    'yellow' => 'bg-yellow-500',
    'red' => 'bg-red-500',
    'purple' => 'bg-purple-500',
    'blue' => 'bg-blue-500',
    'orange' => 'bg-orange-500',
    'teal' => 'bg-teal-500',
    'pink' => 'bg-pink-500',
    'gray' => 'bg-gray-500',
];

$iconMap = [
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
    'network' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />',
    'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'package' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
    'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
    'dollar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'lightning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
    'wifi' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />',
    'alert' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
];

$bgClass = $colorClasses[$color] ?? $colorClasses['indigo'];
$iconSvg = $iconMap[$icon] ?? $iconMap['chart'];

$wrapperClass = $link 
    ? 'group bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out cursor-pointer border border-gray-100 dark:border-gray-700' 
    : 'bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700';
@endphp

@if($link)
<a href="{{ $link }}" class="{{ $wrapperClass }}">
@else
<div class="{{ $wrapperClass }}">
@endif
    <div class="p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 {{ $bgClass }} rounded-lg p-3 shadow-md {{ $link ? 'group-hover:scale-110 group-hover:shadow-lg transition-all duration-300' : '' }}">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    {!! $iconSvg !!}
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">
                        {{ $title }}
                        @if($link)
                        <svg class="inline-block w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        @endif
                    </dt>
                    <dd class="mt-1">
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $value }}
                        </div>
                        @if($subtitle)
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $subtitle }}
                        </div>
                        @endif
                        @if($trend && $trendValue)
                        <div class="flex items-center mt-2">
                            @if($trend === 'up')
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span class="ml-1 text-sm font-medium text-green-600">{{ $trendValue }}</span>
                            @elseif($trend === 'down')
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            <span class="ml-1 text-sm font-medium text-red-600">{{ $trendValue }}</span>
                            @else
                            <span class="ml-1 text-sm font-medium text-gray-600">{{ $trendValue }}</span>
                            @endif
                        </div>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
@if($link)
</a>
@else
</div>
@endif
