@extends('panels.layouts.app')

@section('title', 'Operator Profile')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Operator Profile</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View and manage operator information</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.admin.operators.edit', $operator->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profile
                    </a>
                    <a href="{{ route('panel.admin.operators') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center">
                        <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <span class="text-white font-bold text-2xl">{{ substr($operator->name, 0, 2) }}</span>
                        </div>
                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $operator->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $operator->email }}</p>
                        
                        <div class="mt-4 flex justify-center space-x-2">
                            @php
                                $role = $operator->roles->first();
                                $roleColors = [
                                    'manager' => 'bg-purple-100 text-purple-800',
                                    'staff' => 'bg-blue-100 text-blue-800',
                                    'reseller' => 'bg-green-100 text-green-800',
                                    'sub-reseller' => 'bg-yellow-100 text-yellow-800',
                                ];
                                $colorClass = $roleColors[$role->slug ?? ''] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $colorClass }}">
                                {{ $role->name ?? 'No Role' }}
                            </span>
                            @if($operator->is_active)
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <div class="mt-6 space-y-2">
                            <a href="{{ route('panel.admin.operators.special-permissions', $operator->id) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Manage Permissions
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Quick Info</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Employee ID</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $operator->employee_id ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Department</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($operator->department ?? 'N/A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Joined</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $operator->joining_date ?? $operator->created_at->format('M d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $operator->last_login_at ? $operator->last_login_at->diffForHumans() : 'Never' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Personal Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Employee ID</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->employee_id ?? 'Not assigned' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Address</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->address ?? 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role & Department -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Role & Department
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Role</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->roles->first()->name ?? 'No Role Assigned' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Department</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($operator->department ?? 'Not assigned') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Reports To</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->supervisor->name ?? 'No Supervisor' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Joining Date</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $operator->joining_date ?? $operator->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity & Stats -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Activity & Statistics
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['customers_created'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Customers Created</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['tickets_resolved'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tickets Resolved</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_logins'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Logins</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['days_active'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Days Active</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Recent Activity
                    </h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Logged in to system</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                <time>{{ $operator->last_login_at ? $operator->last_login_at->diffForHumans() : 'N/A' }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Account created</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                <time>{{ $operator->created_at->format('M d, Y') }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
