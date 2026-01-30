@extends('layouts.panel')

@section('title', 'Edit API Key')

@section('content')
<div class="w-full px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Edit API Key</h3>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('api-keys.update', $apiKey) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('name', $apiKey->name) }}" required>
                        @error('name')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rate Limit (requests per minute)</label>
                        <input type="number" name="rate_limit" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('rate_limit', $apiKey->rate_limit) }}" min="1" max="1000">
                        @error('rate_limit')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiration Date</label>
                        <input type="date" name="expires_at" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('expires_at', $apiKey->expires_at?->format('Y-m-d')) }}">
                        @error('expires_at')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <div class="flex items-center">
                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $apiKey->is_active) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm text-gray-900 dark:text-gray-100" for="isActive">Active</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permissions</label>
                        <div class="flex items-center mb-2">
                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" type="checkbox" name="permissions[]" value="invoices.read" id="perm1" {{ in_array('invoices.read', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm text-gray-900 dark:text-gray-100" for="perm1">Read Invoices</label>
                        </div>
                        <div class="flex items-center mb-2">
                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" type="checkbox" name="permissions[]" value="payments.create" id="perm2" {{ in_array('payments.create', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm text-gray-900 dark:text-gray-100" for="perm2">Create Payments</label>
                        </div>
                        <div class="flex items-center mb-2">
                            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" type="checkbox" name="permissions[]" value="customers.read" id="perm3" {{ in_array('customers.read', $apiKey->permissions ?? []) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm text-gray-900 dark:text-gray-100" for="perm3">Read Customers</label>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">Update API Key</button>
                        <a href="{{ route('api-keys.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
