<div class="p-4">
    <h4 class="text-lg font-semibold mb-3">{{ $package->name }} - Fair Usage Policy</h4>
    
    <div class="space-y-4">
        @if($package->data_limit)
            <div class="border-l-4 border-blue-500 pl-4 py-2">
                <h5 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Data Limit</h5>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ number_format($package->data_limit / (1024 * 1024 * 1024), 2) }} GB per month
                </p>
            </div>
        @endif

        @if($package->bandwidth_up || $package->bandwidth_down)
            <div class="border-l-4 border-green-500 pl-4 py-2">
                <h5 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Bandwidth</h5>
                <p class="text-gray-600 dark:text-gray-400">
                    Download: {{ $package->bandwidth_down ?? 'Unlimited' }} Mbps<br>
                    Upload: {{ $package->bandwidth_up ?? 'Unlimited' }} Mbps
                </p>
            </div>
        @endif

        @if($package->validity_days)
            <div class="border-l-4 border-yellow-500 pl-4 py-2">
                <h5 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Validity</h5>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $package->validity_days }} days
                </p>
            </div>
        @endif

        <div class="border-l-4 border-purple-500 pl-4 py-2">
            <h5 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Price</h5>
            <p class="text-gray-600 dark:text-gray-400">
                ${{ number_format($package->price, 2) }} / {{ ucfirst($package->billing_type) }}
            </p>
        </div>

        @if($package->description)
            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded">
                <h5 class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-2">Description</h5>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $package->description }}</p>
            </div>
        @endif

        <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            <p>* Fair usage policies apply to ensure optimal service for all users</p>
            <p>* Excessive usage may result in temporary speed throttling</p>
        </div>
    </div>
</div>
