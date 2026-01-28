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

    <!-- View Toggle -->
    <div class="flex justify-end mb-4">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <button type="button" onclick="showCardView()" 
                    id="cardViewBtn"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Cards
            </button>
            <button type="button" onclick="showTableView()"
                    id="tableViewBtn"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Table
            </button>
        </div>
    </div>

    <!-- Card View (Task 14.1: Update billing profile cards) -->
    <div id="cardView" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($profiles as $profile)
                <x-billing-profile-card :profile="$profile" />
            @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No billing profiles found.</p>
                </div>
            @endforelse
        </div>
        @if($profiles->hasPages())
            <div class="mt-6">{{ $profiles->links() }}</div>
        @endif
    </div>

    <!-- Profiles Table -->
    <div id="tableView" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
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
                                    @if($profile->type === 'daily') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($profile->type === 'monthly') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                    @endif">
                                    {{ ucfirst($profile->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                @if($profile->type === 'monthly' && isset($profile->due_date_figure))
                                    <span class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $profile->due_date_figure }}</span>
                                @else
                                    {{ $profile->schedule_description }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $profile->users_count ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($profile->is_active)
                                    <span class="px-2 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</span>
                                @else
                                    <span class="px-2 text-xs rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="{{ route('panel.admin.billing-profiles.show', $profile) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 mr-3">View</a>
                                <a href="{{ route('panel.admin.billing-profiles.edit', $profile) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 mr-3">Edit</a>
                                <form action="{{ route('panel.admin.billing-profiles.destroy', $profile) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-red-600 dark:text-red-400 @if($profile->users_count > 0) opacity-50 cursor-not-allowed @endif"
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
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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

<script>
function showCardView() {
    document.getElementById('cardView').classList.remove('hidden');
    document.getElementById('tableView').classList.add('hidden');
    document.getElementById('cardViewBtn').classList.add('bg-indigo-600', 'text-white');
    document.getElementById('cardViewBtn').classList.remove('bg-white', 'text-gray-700', 'dark:bg-gray-800', 'dark:text-gray-300');
    document.getElementById('tableViewBtn').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('tableViewBtn').classList.add('bg-white', 'text-gray-700', 'dark:bg-gray-800', 'dark:text-gray-300');
    localStorage.setItem('billingProfileView', 'card');
}

function showTableView() {
    document.getElementById('cardView').classList.add('hidden');
    document.getElementById('tableView').classList.remove('hidden');
    document.getElementById('tableViewBtn').classList.add('bg-indigo-600', 'text-white');
    document.getElementById('tableViewBtn').classList.remove('bg-white', 'text-gray-700', 'dark:bg-gray-800', 'dark:text-gray-300');
    document.getElementById('cardViewBtn').classList.remove('bg-indigo-600', 'text-white');
    document.getElementById('cardViewBtn').classList.add('bg-white', 'text-gray-700', 'dark:bg-gray-800', 'dark:text-gray-300');
    localStorage.setItem('billingProfileView', 'table');
}

// Restore view preference on page load
document.addEventListener('DOMContentLoaded', function() {
    const viewPreference = localStorage.getItem('billingProfileView') || 'table';
    if (viewPreference === 'card') {
        showCardView();
    } else {
        showTableView();
    }
});
</script>
@endsection
