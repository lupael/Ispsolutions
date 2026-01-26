@extends('panels.layouts.app')

@section('title', 'Billing Profiles')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Billing Profiles</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage billing schedules and configurations</p>
                </div>
                <a href="{{ route('panel.admin.billing-profiles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Profile
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-500 p-4 rounded">
            <p class="text-sm text-green-700 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Profiles Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Schedule</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($profiles as $profile)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $profile->name }}</div>
                                @if($profile->description)
                                    <div class="text-sm text-gray-500">{{ Str::limit($profile->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 text-xs rounded-full 
                                    @if($profile->type === 'daily') bg-blue-100 text-blue-800
                                    @elseif($profile->type === 'monthly') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($profile->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $profile->schedule_description }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $profile->users_count }}
                            </td>
                            <td class="px-6 py-4">
                                @if($profile->is_active)
                                    <span class="px-2 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 text-xs rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('panel.admin.billing-profiles.show', $profile) }}" class="text-indigo-600 mr-3">View</a>
                                <a href="{{ route('panel.admin.billing-profiles.edit', $profile) }}" class="text-blue-600 mr-3">Edit</a>
                                <form action="{{ route('panel.admin.billing-profiles.destroy', $profile) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-red-600 @if($profile->users_count > 0) opacity-50 cursor-not-allowed @endif"
                                        @if($profile->users_count > 0)
                                            disabled
                                            title="Cannot delete profile with assigned customers"
                                        @endif
                                    >
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No billing profiles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($profiles->hasPages())
                <div class="mt-4">{{ $profiles->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
