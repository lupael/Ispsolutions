@extends('panels.layouts.app')

@section('title', 'Sub-Operators')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Sub-Operators & Hierarchy</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">View reporting structure and operator hierarchy</p>
                </div>
                <div>
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

    <!-- Hierarchy View -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Organization Hierarchy</h3>
            
            <!-- Hierarchy Tree -->
            <div class="space-y-4">
                @forelse($hierarchy as $supervisor)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <!-- Supervisor -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                                    <span class="text-white font-semibold">{{ substr($supervisor->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $supervisor->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $supervisor->email }}</p>
                                        <div class="mt-1">
                                            @php
                                                $role = $supervisor->roles->first();
                                                $roleColors = [
                                                    'manager' => 'bg-purple-100 text-purple-800',
                                                    'staff' => 'bg-blue-100 text-blue-800',
                                                ];
                                                $colorClass = $roleColors[$role->slug ?? ''] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $colorClass }}">
                                                {{ $role->name ?? 'No Role' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('panel.admin.operators.profile', $supervisor->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm">View</a>
                                        <a href="{{ route('panel.admin.operators.edit', $supervisor->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-sm">Edit</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Operators -->
                        @if(isset($supervisor->subordinates) && $supervisor->subordinates->count() > 0)
                            <div class="ml-16 mt-4 space-y-3">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    Reports To This Manager ({{ $supervisor->subordinates->count() }})
                                </div>
                                @foreach($supervisor->subordinates as $subordinate)
                                    <div class="border-l-2 border-indigo-300 dark:border-indigo-700 pl-4 py-2">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                                    <span class="text-white text-xs font-semibold">{{ substr($subordinate->name, 0, 2) }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $subordinate->name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subordinate->email }}</p>
                                                </div>
                                                <div>
                                                    @php
                                                        $subRole = $subordinate->roles->first();
                                                        $subRoleColors = [
                                                            'staff' => 'bg-blue-100 text-blue-800',
                                                        ];
                                                        $subColorClass = $subRoleColors[$subRole->slug ?? ''] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $subColorClass }}">
                                                        {{ $subRole->name ?? 'No Role' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('panel.admin.operators.profile', $subordinate->id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs">View</a>
                                                <a href="{{ route('panel.admin.operators.edit', $subordinate->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 text-xs">Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No hierarchy structure found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Supervisors</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['supervisors'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Subordinates</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['subordinates'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg. Team Size</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['avg_team_size'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
