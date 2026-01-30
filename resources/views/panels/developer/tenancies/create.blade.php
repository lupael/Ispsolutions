@extends('panels.layouts.app')

@section('title', 'Create Tenancy')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Tenancy</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Create a new ISP/Super Admin tenancy</p>
            </div>
            <a href="{{ route('panel.developer.tenancies.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <form action="{{ route('panel.developer.tenancies.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tenancy Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The name of the ISP or organization</p>
                        </div>

                        <div class="mb-4">
                            <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Domain</label>
                            <input type="text" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('domain') border-red-500 @enderror" 
                                   id="domain" name="domain" value="{{ old('domain') }}" 
                                   placeholder="example.com">
                            @error('domain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Full domain for this tenancy (optional)</p>
                        </div>

                        <div class="mb-4">
                            <label for="subdomain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subdomain</label>
                            <input type="text" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('subdomain') border-red-500 @enderror" 
                                   id="subdomain" name="subdomain" value="{{ old('subdomain') }}" 
                                   placeholder="example">
                            @error('subdomain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Subdomain prefix (optional)</p>
                        </div>

                        <div class="mb-4">
                            <label for="database" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Database Name</label>
                            <input type="text" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('database') border-red-500 @enderror" 
                                   id="database" name="database" value="{{ old('database') }}" 
                                   placeholder="isp_database">
                            @error('database')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Database name for this tenancy (optional)</p>
                        </div>

                        <div class="mb-6">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status <span class="text-red-600">*</span>
                            </label>
                            <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror" 
                                    id="status" name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 my-6"></div>
                        <h5 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Super Admin Account</h5>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">A Super Admin account will be automatically created for this tenancy.</p>

                        <div class="mb-4">
                            <label for="super_admin_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Super Admin Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('super_admin_name') border-red-500 @enderror" 
                                   id="super_admin_name" name="super_admin_name" value="{{ old('super_admin_name') }}" required>
                            @error('super_admin_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="super_admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Super Admin Email <span class="text-red-600">*</span>
                            </label>
                            <input type="email" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('super_admin_email') border-red-500 @enderror" 
                                   id="super_admin_email" name="super_admin_email" value="{{ old('super_admin_email') }}" required>
                            @error('super_admin_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="super_admin_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Super Admin Password <span class="text-red-600">*</span>
                            </label>
                            <input type="password" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('super_admin_password') border-red-500 @enderror" 
                                   id="super_admin_password" name="super_admin_password" required>
                            @error('super_admin_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                        </div>

                        <div class="mb-6">
                            <label for="super_admin_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Confirm Password <span class="text-red-600">*</span>
                            </label>
                            <input type="password" 
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('super_admin_password_confirmation') border-red-500 @enderror" 
                                   id="super_admin_password_confirmation" name="super_admin_password_confirmation" required>
                            @error('super_admin_password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="{{ route('panel.developer.tenancies.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Create Tenancy
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
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        A tenancy represents a separate ISP or organization within the system.
                    </p>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Isolated data and users</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Custom domain/subdomain</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Separate database (optional)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700 dark:text-gray-300">Full access control</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
