@extends('panels.layouts.app')

@section('title', 'Roles Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Roles Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage system roles and permissions</p>
                </div>
                <div>
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Add New Role
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($roles as $role)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $role->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $role->description }}</p>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $role->users_count }} users</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
