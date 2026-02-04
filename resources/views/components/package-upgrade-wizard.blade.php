@props([
    'customer',
    'currentPackage' => null
])

@php
    // Get current package if not provided
    if (!$currentPackage && $customer) {
        $currentPackage = $customer->package;
    }
    
    // Get available upgrade options
    $upgradeOptions = [];
    if ($currentPackage) {
        $upgradeOptions = \App\Models\Package::where('status', 'active')
            ->where('id', '!=', $currentPackage->id)
            ->where('price', '>=', $currentPackage->price)
            ->orderBy('price', 'asc')
            ->get()
            ->filter(function($package) use ($currentPackage) {
                return $currentPackage->canUpgradeTo($package);
            });
    }
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                {{ __('packages.upgrade_wizard') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('packages.upgrade_wizard_help') }}
            </p>
        </div>

        @if($currentPackage)
            <!-- Current Package -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border-2 border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Current Package') }}</span>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $currentPackage->name }}</h4>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ __('Speed') }}:</span> 
                                {{ $currentPackage->bandwidth_download }}{{ $currentPackage->readable_rate_unit ?? 'Mbps' }} 
                                <svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                </svg>
                                {{ $currentPackage->bandwidth_upload }}{{ $currentPackage->readable_rate_unit ?? 'Mbps' }}
                                <svg class="inline w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                </svg>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ __('Validity') }}:</span> 
                                {{ $currentPackage->validity }} {{ ucfirst($currentPackage->validity_unit) }}
                            </p>
                            @if($currentPackage->data_limit)
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">{{ __('Data Limit') }}:</span> 
                                    {{ $currentPackage->data_limit }} {{ $currentPackage->data_limit_unit }}
                                </p>
                            @else
                                <p class="text-sm text-green-600 dark:text-green-400">
                                    <span class="font-medium">{{ __('Data') }}:</span> {{ __('Unlimited') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                            ${{ number_format($currentPackage->price, 2) }}
                        </span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('/month') }}</p>
                    </div>
                </div>
            </div>

            <!-- Upgrade Options -->
            @if($upgradeOptions->count() > 0)
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        {{ __('Available Upgrades') }}
                    </h4>

                    @foreach($upgradeOptions as $option)
                        @php
                            $priceDiff = $option->price - $currentPackage->price;
                            $speedIncrease = $option->bandwidth_download - $currentPackage->bandwidth_download;
                            $speedPercentage = $currentPackage->bandwidth_download > 0 
                                ? round(($speedIncrease / $currentPackage->bandwidth_download) * 100) 
                                : 0;
                        @endphp
                        
                        <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h5 class="font-bold text-gray-900 dark:text-gray-100 text-lg">{{ $option->name }}</h5>
                                        @if($speedIncrease > 0)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                +{{ $speedIncrease }}Mbps
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-3 grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">{{ __('Speed') }}:</span> 
                                                <span class="text-green-600 dark:text-green-400 font-semibold">
                                                    {{ $option->bandwidth_download }}{{ $option->readable_rate_unit ?? 'Mbps' }}
                                                </span>
                                                @if($speedPercentage > 0)
                                                    <span class="text-xs text-gray-500">(+{{ $speedPercentage }}%)</span>
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <span class="font-medium">{{ __('Validity') }}:</span> 
                                                {{ $option->validity }} {{ ucfirst($option->validity_unit) }}
                                            </p>
                                        </div>
                                        <div>
                                            @if($option->data_limit)
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    <span class="font-medium">{{ __('Data') }}:</span> 
                                                    {{ $option->data_limit }} {{ $option->data_limit_unit }}
                                                </p>
                                            @else
                                                <p class="text-sm text-green-600 dark:text-green-400">
                                                    <span class="font-medium">{{ __('Data') }}:</span> {{ __('Unlimited') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    @if($option->features || $option->description)
                                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                            @if($option->features)
                                                <ul class="list-disc list-inside space-y-1">
                                                    @foreach(explode(',', $option->features) as $feature)
                                                        <li>{{ trim($feature) }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-6 text-right">
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                        ${{ number_format($option->price, 2) }}
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('/month') }}</p>
                                    <div class="mt-2 text-sm">
                                        <span class="text-orange-600 dark:text-orange-400 font-semibold">
                                            +${{ number_format($priceDiff, 2) }}
                                        </span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('additional') }}</p>
                                    </div>
                                    
                                    <button type="button" 
                                            onclick="confirmUpgrade({{ $customer->id ?? 0 }}, {{ $currentPackage->id }}, {{ $option->id }}, '{{ addslashes($option->name) }}', {{ $priceDiff }})"
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            aria-label="{{ __('Upgrade to') }} {{ $option->name }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                        {{ __('Upgrade Now') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('packages.no_upgrades_available') }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('packages.already_on_best_package') }}
                    </p>
                </div>
            @endif
        @else
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('packages.no_package_selected') }}
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Please select a current package first') }}
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
function confirmUpgrade(customerId, currentPackageId, targetPackageId, targetPackageName, priceDiff) {
    if (!customerId) {
        alert('{{ __("Customer ID is required") }}');
        return;
    }

    const confirmed = confirm(
        `{{ __("packages.confirm_upgrade") }}\n\n` +
        `{{ __("New Package") }}: ${targetPackageName}\n` +
        `{{ __("Additional Cost") }}: $${priceDiff.toFixed(2)}/month\n\n` +
        `{{ __("Do you want to proceed?") }}`
    );

    if (confirmed) {
        // Generate URL using route name (Laravel will handle this when rendered)
        const url = `{{ route('panel.isp.customers.package-change.store', ['customer' => '__CUSTOMER_ID__']) }}`.replace('__CUSTOMER_ID__', customerId);
        
        // Submit upgrade request
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                new_package_id: targetPackageId,
                effective_date: 'immediate'
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                alert('{{ __("Package upgraded successfully!") }}');
                window.location.reload();
            } else {
                const message = data && data.message ? data.message : '{{ __("Unknown error occurred") }}';
                alert('{{ __("Error") }}: ' + message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
        });
    }
}
</script>
@endpush
