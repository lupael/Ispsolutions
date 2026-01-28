@extends('panels.layouts.app')

@section('title', __('packages.hierarchy_view'))

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ __('packages.hierarchy_view') }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        {{ __('packages.hierarchy_help') }}
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

    <!-- Package Hierarchy Tree -->
    <x-package-hierarchy-tree :packages="$packageTree" />
</div>
@endsection
