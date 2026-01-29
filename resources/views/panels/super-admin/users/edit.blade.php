@extends('panels.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Edit User</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Update user information for {{ $user->name }}</p>
                </div>
                <div>
                    <a href="{{ route('panel.super-admin.users') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('panel.super-admin.users.update', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Personal Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            @php $currentRoleId = $user->roles->first()->id ?? ''; @endphp
                            <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role <span class="text-red-500">*</span></label>
                            <select name="role_id" id="role_id" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $currentRoleId) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Account Security</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                            <input type="password" name="password" id="password" placeholder="Leave blank to keep current password" autocomplete="new-password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to keep current password</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('panel.super-admin.users') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
