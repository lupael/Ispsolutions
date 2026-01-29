@extends('panels.layouts.app')

@section('title', 'Create Subscription Plan')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Subscription Plan</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new subscription plan for ISP clients</p>
            </div>
            <a href="{{ route('panel.developer.subscriptions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                Back to List
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <form action="{{ route('panel.developer.subscriptions.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug *</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           placeholder="e.g., basic-plan">
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price *</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Cycle *</label>
                    <select name="billing_cycle" id="billing_cycle" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('billing_cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>

                <div>
                    <label for="max_users" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Users</label>
                    <input type="number" name="max_users" id="max_users" value="{{ old('max_users') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           placeholder="Leave blank for unlimited">
                </div>

                <div>
                    <label for="max_routers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Routers</label>
                    <input type="number" name="max_routers" id="max_routers" value="{{ old('max_routers') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           placeholder="Leave blank for unlimited">
                </div>

                <div>
                    <label for="max_olts" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max OLTs</label>
                    <input type="number" name="max_olts" id="max_olts" value="{{ old('max_olts') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           placeholder="Leave blank for unlimited">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
            </div>

            <div class="flex gap-2 justify-end">
                <a href="{{ route('panel.developer.subscriptions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                    Create Plan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
