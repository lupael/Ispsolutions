@props(['operatorInfo'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Operator Information</h3>
    </div>
    
    <div class="grid grid-cols-3 gap-4">
        <!-- Total -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
            <p class="text-sm font-medium text-purple-700 dark:text-purple-300 mb-1">Total</p>
            <p class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($operatorInfo['total']) }}</p>
        </div>

        <!-- Active -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
            <p class="text-sm font-medium text-green-700 dark:text-green-300 mb-1">Active</p>
            <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($operatorInfo['active']) }}</p>
        </div>

        <!-- In-Active -->
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">In-Active</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($operatorInfo['inactive']) }}</p>
        </div>
    </div>
</div>
