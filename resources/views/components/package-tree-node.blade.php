@props(['node', 'level' => 0])

<div class="package-tree-node">
    <!-- Package Item -->
    <div class="flex items-center py-3 px-4 rounded-lg border border-gray-200 dark:border-gray-700 mb-2"
         style="margin-left: {{ $node['level'] * 32 }}px;">
        
        <!-- Connector Line (for children) -->
        @if($node['level'] > 0)
            <div class="package-tree-connector"></div>
        @endif

        <!-- Package Icon/Indicator -->
        <div class="flex-shrink-0 mr-3">
            @if(count($node['children']) > 0)
                <!-- Has children - show tree icon -->
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            @else
                <!-- Leaf node - show package icon -->
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            @endif
        </div>

        <!-- Package Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $node['name'] }}
                    </h4>
                    
                    <!-- Status Badge -->
                    <span class="px-2 py-0.5 text-xs rounded-full 
                        {{ $node['status'] === 'active' 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' 
                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' }}">
                        {{ ucfirst($node['status']) }}
                    </span>
                </div>

                <!-- Price and Customer Count -->
                <div class="flex items-center space-x-4 text-sm">
                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                        ${{ number_format($node['price'], 2) }}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ __('packages.customers_using_package', ['count' => $node['customer_count']]) }}
                    </span>
                </div>
            </div>

            <!-- Package Details (collapsed by default) -->
            @if($node['description'] || $node['bandwidth_download'])
                <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                    @if($node['description'])
                        <p class="mb-1">{{ \Illuminate\Support\Str::limit($node['description'], 80) }}</p>
                    @endif
                    @if($node['bandwidth_download'])
                        <span class="inline-flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ $node['bandwidth_download'] }} Mbps
                        </span>
                        @if($node['validity_days'])
                            <span class="mx-2">•</span>
                            <span>{{ $node['validity_days'] }} {{ __('packages.validity_days') }}</span>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex-shrink-0 ml-4">
            <a href="{{ route('panel.admin.master-packages.show', $node['id']) }}" 
               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Render Children Recursively -->
    @if(count($node['children']) > 0)
        <div class="space-y-2">
            @foreach($node['children'] as $child)
                @include('components.package-tree-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
