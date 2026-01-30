@extends('panels.layouts.app')

@section('title', 'Role Names Configuration')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Role Names Configuration</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Customize the display names for user roles in your system</p>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
            <span class="text-green-500">&times;</span>
        </button>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white">System Role Names</h5>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Customize how roles appear throughout the application</p>
        </div>
        <div class="p-6">
            <form action="{{ route('panel.developer.settings.roles.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="operator_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Operator Role Name
                        </label>
                        <input type="text" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="operator_name" 
                               name="operator_name" 
                               value="{{ old('operator_name', $roleNames['operator'] ?? 'Operator') }}"
                               placeholder="Operator">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default: Operator (Operator Level: 30)</p>
                    </div>

                    <div>
                        <label for="sub_operator_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sub-Operator Role Name
                        </label>
                        <input type="text" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="sub_operator_name" 
                               name="sub_operator_name" 
                               value="{{ old('sub_operator_name', $roleNames['sub_operator'] ?? 'Sub-Operator') }}"
                               placeholder="Sub-Operator">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default: Sub-Operator (Operator Level: 40)</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="document.querySelectorAll('input[type=text]').forEach(el => el.value = el.placeholder)" 
                            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                        Reset to Defaults
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Save Role Names
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">About Role Names</h3>
                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                    Custom role names will be displayed throughout the application, including navigation menus, user lists, and permission descriptions. 
                    The underlying operator levels and permissions remain unchanged.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
