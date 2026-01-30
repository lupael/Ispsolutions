@props(['ispInfo'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">ISP Information</h3>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ ucfirst($ispInfo['status']) }}
        </span>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <!-- Total Clients -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <p class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-1">Total Clients</p>
            <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($ispInfo['total_clients']) }}</p>
        </div>

        <!-- Active Clients -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
            <p class="text-sm font-medium text-green-700 dark:text-green-300 mb-1">Active Client</p>
            <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($ispInfo['active_clients']) }}</p>
        </div>

        <!-- In-Active Clients -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300 mb-1">In-Active Client</p>
            <p class="text-3xl font-bold text-yellow-900 dark:text-yellow-100">{{ number_format($ispInfo['inactive_clients']) }}</p>
        </div>

        <!-- Expired Clients -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
            <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-1">Expired Client</p>
            <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ number_format($ispInfo['expired_clients']) }}</p>
        </div>
    </div>
</div>
