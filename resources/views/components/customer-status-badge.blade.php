@props(['status'])

@php
    $colors = [
        'prepaid_active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'postpaid_active' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'prepaid_suspended' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'postpaid_suspended' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'prepaid_expired' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'postpaid_expired' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'prepaid_inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'postpaid_inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    ];

    $icons = [
        'prepaid_active' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'postpaid_active' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'prepaid_suspended' => 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'postpaid_suspended' => 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'prepaid_expired' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'postpaid_expired' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        'prepaid_inactive' => 'M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'postpaid_inactive' => 'M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
    ];

    $labels = [
        'prepaid_active' => 'Prepaid & Active',
        'postpaid_active' => 'Postpaid & Active',
        'prepaid_suspended' => 'Prepaid & Suspended',
        'postpaid_suspended' => 'Postpaid & Suspended',
        'prepaid_expired' => 'Prepaid & Expired',
        'postpaid_expired' => 'Postpaid & Expired',
        'prepaid_inactive' => 'Prepaid & Inactive',
        'postpaid_inactive' => 'Postpaid & Inactive',
    ];

    // Handle both enum objects and string values
    $statusValue = is_object($status) ? $status->value : $status;
    $colorClass = $colors[$statusValue] ?? 'bg-gray-100 text-gray-800';
    $iconPath = $icons[$statusValue] ?? 'M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z';
    $label = $labels[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue));
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$colorClass}"]) }}
      title="{{ $label }}">
    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
    </svg>
    {{ $label }}
</span>
