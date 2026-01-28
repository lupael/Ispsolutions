@props(['packages' => []])

@php
    // Ensure we have at least 2 packages to compare
    $packagesToCompare = collect($packages)->take(4);
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('packages.feature_comparison') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('packages.comparison_help') }}
            </p>
        </div>

        @if($packagesToCompare->count() >= 2)
            <!-- Comparison Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50">
                                {{ __('Feature') }}
                            </th>
                            @foreach($packagesToCompare as $package)
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50">
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $package->name }}</div>
                                        <div class="text-indigo-600 dark:text-indigo-400 font-bold mt-1">
                                            ${{ number_format($package->price, 2) }}
                                        </div>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Download Speed -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.download_speed') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $package->bandwidth_download ?? 'N/A' }} Mbps
                                </td>
                            @endforeach
                        </tr>

                        <!-- Upload Speed -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.upload_speed') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $package->bandwidth_upload ?? 'N/A' }} Mbps
                                </td>
                            @endforeach
                        </tr>

                        <!-- Data Limit -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.data_limit') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @if($package->data_limit)
                                        {{ $package->data_limit }} GB
                                    @else
                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ __('Unlimited') }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        <!-- Validity -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.validity') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $package->validity_days ?? 'N/A' }} {{ __('days') }}
                                </td>
                            @endforeach
                        </tr>

                        <!-- Service Type -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.service_type') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $package->service_type === 'pppoe' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                        {{ strtoupper($package->service_type ?? 'N/A') }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>

                        <!-- Status -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.status') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $package->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' }}">
                                        {{ ucfirst($package->status) }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>

                        <!-- Customer Count -->
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('packages.customer_count') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-semibold">{{ $package->customer_count }}</span>
                                    {{ __('customers') }}
                                </td>
                            @endforeach
                        </tr>

                        <!-- Actions -->
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Actions') }}
                            </td>
                            @foreach($packagesToCompare as $package)
                                <td class="px-4 py-4">
                                    <div class="flex flex-col space-y-2">
                                        <a href="{{ route('panel.admin.master-packages.show', $package->id) }}" 
                                           class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                            {{ __('packages.package_details') }}
                                        </a>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <!-- Not enough packages -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ __('packages.select_packages_to_compare') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Select at least 2 packages to compare features') }}
                </p>
            </div>
        @endif
    </div>
</div>
