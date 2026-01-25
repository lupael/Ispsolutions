@extends('panels.layouts.app')

@section('title', 'Expired User Pool Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Expired User Pool</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage expired and expiring user accounts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Expired Today</div>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">0</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
