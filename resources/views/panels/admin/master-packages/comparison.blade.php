@extends('panels.layouts.app')

@section('title', __('packages.comparison'))

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ __('packages.comparison') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('packages.comparison_help') }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.admin.master-packages.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Packages') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Selection -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ __('packages.select_packages_to_compare') }}
            </h3>
            
            <form action="{{ route('panel.admin.master-packages.comparison') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($allPackages as $package)
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 
                            {{ in_array($package->id, $packages->pluck('id')->toArray()) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-gray-600' }}">
                            <input 
                                type="checkbox" 
                                name="packages[]" 
                                value="{{ $package->id }}"
                                {{ in_array($package->id, $packages->pluck('id')->toArray()) ? 'checked' : '' }}
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                onchange="this.form.submit()"
                            >
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $package->name }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    ${{ number_format($package->price, 2) }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
                
                @if($packages->count() >= 2)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __(':count packages selected for comparison', ['count' => $packages->count()]) }}
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Package Comparison Table -->
    @if($packages->count() >= 2)
        <x-package-comparison :packages="$packages" />
    @else
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('packages.select_packages_to_compare') }}
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Select at least 2 packages from the list above to compare their features') }}
                </p>
            </div>
        </div>
    @endif
</div>
@endsection
