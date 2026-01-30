@props(['subOperatorClients'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Clients of Sub-Operator</h3>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <!-- Total Clients -->
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 border border-indigo-200 dark:border-indigo-700 rounded-lg p-4">
            <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-1">Total Clients</p>
            <p class="text-3xl font-bold text-indigo-900 dark:text-indigo-100">{{ number_format($subOperatorClients['total_clients']) }}</p>
        </div>

        <!-- Active Clients -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
            <p class="text-sm font-medium text-green-700 dark:text-green-300 mb-1">Active Clients</p>
            <p class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($subOperatorClients['active_clients']) }}</p>
        </div>

        <!-- In-Active Clients -->
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300 mb-1">In-Active Clients</p>
            <p class="text-3xl font-bold text-yellow-900 dark:text-yellow-100">{{ number_format($subOperatorClients['inactive_clients']) }}</p>
        </div>

        <!-- Expired Clients -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
            <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-1">Expired Clients</p>
            <p class="text-3xl font-bold text-red-900 dark:text-red-100">{{ number_format($subOperatorClients['expired_clients']) }}</p>
        </div>
    </div>
</div>
