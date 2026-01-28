@props(['packages', 'threshold' => 5])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Low-Performing Packages
        </h3>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
            {{ $packages->count() }}
        </span>
    </div>
    
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Packages with fewer than {{ $threshold }} customers
    </p>
    
    @if($packages->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">All packages are performing well</p>
        </div>
    @else
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($packages as $package)
                @php
                    $customerCount = $package->users_count ?? 0;
                    
                    if ($customerCount === 0) {
                        $statusColor = 'red';
                        $statusText = 'No customers';
                    } elseif ($customerCount === 1) {
                        $statusColor = 'orange';
                        $statusText = '1 customer';
                    } else {
                        $statusColor = 'yellow';
                        $statusText = $customerCount . ' customers';
                    }
                    
                    $colorClasses = [
                        'red' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                        'orange' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
                        'yellow' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                    ];
                    
                    $badgeClasses = [
                        'red' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    ];
                @endphp
                
                <div class="border {{ $colorClasses[$statusColor] }} rounded-lg p-3">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('panel.admin.packages.edit', $package) }}" 
                               class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $package->name }}
                            </a>
                            @if($package->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ Str::limit($package->description, 50) }}
                                </p>
                            @endif
                            <div class="flex items-center space-x-3 mt-2 text-xs text-gray-600 dark:text-gray-300">
                                @if($package->price)
                                    <span>
                                        <span class="font-medium">Price:</span> ${{ number_format($package->price, 2) }}
                                    </span>
                                @endif
                                @if($package->validity_days)
                                    <span>
                                        <span class="font-medium">Validity:</span> {{ $package->validity_days }} days
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end ml-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $badgeClasses[$statusColor] }}">
                                {{ $statusText }}
                            </span>
                            @if($package->status !== 'active')
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ ucfirst($package->status ?? 'inactive') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Recommendations -->
                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-amber-600 dark:text-amber-400 flex items-start">
                            <svg class="w-3 h-3 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            @if($customerCount === 0)
                                Consider updating pricing or features, or marking as inactive
                            @else
                                Review if package meets market needs
                            @endif
                        </p>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="flex space-x-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('panel.admin.packages.edit', $package) }}" 
                           class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                            Edit Package
                        </a>
                        @if($customerCount === 0)
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <form action="{{ route('panel.admin.packages.destroy', $package) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this package?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($packages->count() > 5)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                <a href="{{ route('panel.admin.packages.index', ['low_performing' => true]) }}" 
                   class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                    View All {{ $packages->count() }} Low-Performing Packages →
                </a>
            </div>
        @endif
    @endif
</div>
