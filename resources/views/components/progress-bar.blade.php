@props([
    'current' => 0,
    'total' => 100,
    'label' => '',
    'showPercentage' => true,
    'showLabel' => true,
    'height' => 'h-6',
    'animated' => false,
    'striped' => false,
])

@php
    $percentage = $total > 0 ? min(100, round(($current / $total) * 100, 1)) : 0;
    
    // Determine color class based on threshold
    $colorClass = match(true) {
        $percentage >= 90 => 'bg-red-600',
        $percentage >= 70 => 'bg-yellow-500',
        default => 'bg-green-600',
    };
    
    // Format label
    $displayLabel = $label ?: "$current / $total";
@endphp

<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full {{ $height }} overflow-hidden">
    <div class="{{ $colorClass }} {{ $height }} rounded-full flex items-center justify-center text-white text-xs font-semibold transition-all duration-300" 
         style="width: {{ $percentage }}%"
         role="progressbar" 
         aria-valuenow="{{ $current }}" 
         aria-valuemin="0" 
         aria-valuemax="{{ $total }}">
        @if($showLabel || $showPercentage)
            <span class="px-2">
                @if($showLabel)
                    {{ $displayLabel }}
                @endif
                @if($showPercentage && $showLabel)
                    &nbsp;({{ $percentage }}%)
                @elseif($showPercentage)
                    {{ $percentage }}%
                @endif
            </span>
        @endif
    </div>
</div>
