@props(['customer'])

@php
    use Carbon\Carbon;
    
    if (!$customer->expiry_date) {
        $expiryDate = null;
        $daysRemaining = null;
        $percentage = 100;
        $color = 'green';
        $message = 'No expiration';
    } else {
        $timezone = $customer->billingProfile?->timezone ?? config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);
        $expiryDate = Carbon::parse($customer->expiry_date)->timezone($timezone);
        
        // Calculate days remaining
        $daysRemaining = $now->diffInDays($expiryDate, false);
        
        // For percentage, assume 30 days is 100%
        $totalDays = 30;
        if ($customer->activated_at) {
            $activatedDate = Carbon::parse($customer->activated_at)->timezone($timezone);
            $totalDays = max($activatedDate->diffInDays($expiryDate), 1);
        }
        
        $daysPassed = $now->diffInDays(Carbon::parse($customer->activated_at ?? $customer->created_at)->timezone($timezone));
        $percentage = max(0, min(100, 100 - (($daysPassed / $totalDays) * 100)));
        
        // Determine color based on days remaining
        if ($daysRemaining < 0) {
            $color = 'red';
            $message = 'Expired ' . abs($daysRemaining) . ' ' . Str::plural('day', abs($daysRemaining)) . ' ago';
        } elseif ($expiryDate->isToday()) {
            $color = 'red';
            $message = 'Expires today!';
        } elseif ($daysRemaining <= 3) {
            $color = 'red';
            $message = 'Expires in ' . $daysRemaining . ' ' . Str::plural('day', $daysRemaining);
        } elseif ($daysRemaining <= 7) {
            $color = 'orange';
            $message = 'Expires in ' . $daysRemaining . ' ' . Str::plural('day', $daysRemaining);
        } else {
            $color = 'green';
            $message = 'Expires in ' . $daysRemaining . ' ' . Str::plural('day', $daysRemaining);
        }
    }

    $colorClasses = [
        'green' => 'bg-green-500',
        'orange' => 'bg-orange-500',
        'red' => 'bg-red-500',
    ];
    
    $bgColorClasses = [
        'green' => 'bg-green-100 dark:bg-green-900/20',
        'orange' => 'bg-orange-100 dark:bg-orange-900/20',
        'red' => 'bg-red-100 dark:bg-red-900/20',
    ];
    
    $textColorClasses = [
        'green' => 'text-green-700 dark:text-green-300',
        'orange' => 'text-orange-700 dark:text-orange-300',
        'red' => 'text-red-700 dark:text-red-300',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <div class="flex justify-between items-center text-sm">
        <span class="font-medium text-gray-700 dark:text-gray-300">Validity Status</span>
        @if($expiryDate)
            <span class="{{ $textColorClasses[$color] }} font-semibold">{{ $message }}</span>
        @else
            <span class="text-gray-500 dark:text-gray-400">{{ $message }}</span>
        @endif
    </div>
    
    @if($expiryDate)
        <!-- Progress Bar -->
        <div class="relative w-full h-4 {{ $bgColorClasses[$color] }} rounded-full overflow-hidden">
            <div class="absolute inset-0 h-full {{ $colorClasses[$color] }} rounded-full transition-all duration-500"
                 style="width: {{ $percentage }}%"></div>
        </div>
        
        <!-- Timeline Labels -->
        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>
                @if($customer->activated_at)
                    Started: {{ Carbon::parse($customer->activated_at)->format('M d, Y') }}
                @else
                    Created: {{ Carbon::parse($customer->created_at)->format('M d, Y') }}
                @endif
            </span>
            <span>Expires: {{ $expiryDate->format('M d, Y') }}</span>
        </div>
        
        <!-- Percentage Display -->
        <div class="text-center">
            <span class="text-lg font-bold {{ $textColorClasses[$color] }}">{{ number_format($percentage, 0) }}%</span>
            <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">remaining</span>
        </div>
    @endif
</div>
