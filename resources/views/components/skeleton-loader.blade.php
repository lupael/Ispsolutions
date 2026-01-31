@props([
    'type' => 'text', // text, card, table, avatar
    'lines' => 3,
    'width' => 'full',
])

@php
$widthClasses = [
    'full' => 'w-full',
    '3/4' => 'w-3/4',
    '1/2' => 'w-1/2',
    '1/4' => 'w-1/4',
    '1/3' => 'w-1/3',
    '2/3' => 'w-2/3',
];
$widthClass = $widthClasses[$width] ?? $widthClasses['full'];
@endphp

@if($type === 'text')
    <div class="animate-pulse space-y-3">
        @for($i = 0; $i < $lines; $i++)
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded {{ $i === $lines - 1 && $lines > 1 ? 'w-2/3' : $widthClass }}"></div>
        @endfor
    </div>
@elseif($type === 'card')
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700 p-6 animate-pulse">
        <div class="flex items-center space-x-4">
            <div class="rounded-lg bg-gray-200 dark:bg-gray-700 h-12 w-12"></div>
            <div class="flex-1 space-y-3">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
        </div>
    </div>
@elseif($type === 'table')
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700 animate-pulse">
        <div class="p-6">
            <!-- Header -->
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
            </div>
            <!-- Rows -->
            @for($i = 0; $i < 5; $i++)
            <div class="grid grid-cols-4 gap-4 py-3 border-t border-gray-100 dark:border-gray-700">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
            @endfor
        </div>
    </div>
@elseif($type === 'avatar')
    <div class="animate-pulse flex space-x-4">
        <div class="rounded-full bg-gray-200 dark:bg-gray-700 h-12 w-12"></div>
        <div class="flex-1 space-y-3 py-1">
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        </div>
    </div>
@elseif($type === 'stat-card')
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700 p-6 animate-pulse">
        <div class="flex items-center">
            <div class="flex-shrink-0 rounded-lg bg-gray-200 dark:bg-gray-700 h-12 w-12"></div>
            <div class="ml-5 w-0 flex-1 space-y-3">
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            </div>
        </div>
    </div>
@endif
