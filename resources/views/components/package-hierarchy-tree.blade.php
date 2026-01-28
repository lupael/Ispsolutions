@props(['packages' => null])

@php
    $hierarchyService = app(\App\Services\PackageHierarchyService::class);
    $packageTree = $hierarchyService->buildTree($packages);
@endphp

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('packages.package_tree') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('packages.hierarchy_help') }}
                </p>
            </div>
        </div>

        <!-- Package Tree -->
        @if($packageTree->count() > 0)
            <div class="space-y-2">
                @foreach($packageTree as $package)
                    @include('components.package-tree-node', ['node' => $package])
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ __('No packages found') }}
                </h3>
            </div>
        @endif
    </div>
</div>

<style>
.package-tree-node {
    transition: all 0.2s ease;
}

.package-tree-node:hover {
    background-color: rgba(99, 102, 241, 0.05);
}

.package-tree-connector {
    border-left: 2px solid #e5e7eb;
    border-bottom: 2px solid #e5e7eb;
    width: 20px;
    height: 24px;
    margin-right: 8px;
}

.dark .package-tree-connector {
    border-color: #374151;
}
</style>
