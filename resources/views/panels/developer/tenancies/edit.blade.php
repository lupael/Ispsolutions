@extends('panels.layouts.app')

@section('title', 'Edit Tenancy')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Tenancy</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Update ISP/Super Admin tenancy information</p>
            </div>
            <a href="{{ route('panel.developer.tenancies.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong> Please fix the following issues:
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="absolute top-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
            <span class="text-red-500">&times;</span>
        </button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <form action="{{ route('panel.developer.tenancies.update', $tenancy->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tenancy Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                                   id="name" name="name" value="{{ old('name', $tenancy->name) }}" required>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The name of the ISP or organization</p>
                        </div>

                        <div class="mb-4">
                            <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Domain <span class="text-red-600">*</span>
                            </label>
                            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('domain') border-red-500 @enderror" 
                                   id="domain" name="domain" value="{{ old('domain', $tenancy->domain) }}" 
                                   placeholder="example.com" required>
                            @error('domain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Full domain for this tenancy</p>
                        </div>

                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status <span class="text-red-600">*</span>
                            </label>
                            <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror" 
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $tenancy->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $tenancy->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status', $tenancy->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current operational status</p>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('panel.developer.tenancies.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Update Tenancy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Tenancy Information</h5>
                </div>
                <div class="p-6">
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $tenancy->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $tenancy->created_at->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $tenancy->updated_at->format('Y-m-d H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Users:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $tenancy->users_count ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
