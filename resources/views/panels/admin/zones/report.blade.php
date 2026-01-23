@extends('panels.layouts.app')

@section('title', 'Zone Analytics Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Zone Analytics Report</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Location-based customer distribution and statistics</p>
                </div>
                <a href="{{ route('panel.admin.zones.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    Back to Zones
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Zones</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($totalStats['total_zones']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Zones</p>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($totalStats['active_zones']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                    <p class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($totalStats['total_customers']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Customers</p>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($totalStats['active_customers']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Network Users</p>
                    <p class="mt-2 text-3xl font-bold text-purple-600">{{ number_format($totalStats['total_network_users']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Network</p>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($totalStats['active_network_users']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone Breakdown Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Zone Breakdown</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Zone
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customers
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Active
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Network Users
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Active Network
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Percentage
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($zones as $zone)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $zone->color }}"></div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $zone->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $zone->code }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($zone->customers_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600">
                                    {{ number_format($zone->active_customers_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($zone->network_users_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600">
                                    {{ number_format($zone->active_network_users_count) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                    @php
                                        $percentage = $totalStats['total_customers'] > 0 
                                            ? ($zone->customers_count / $totalStats['total_customers']) * 100 
                                            : 0;
                                    @endphp
                                    <div class="flex items-center justify-center">
                                        <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No zones found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($zones->count() > 0)
                        <tfoot class="bg-gray-50 dark:bg-gray-900 font-semibold">
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">TOTAL</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($totalStats['total_customers']) }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-green-600">
                                    {{ number_format($totalStats['active_customers']) }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-gray-100">
                                    {{ number_format($totalStats['total_network_users']) }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-green-600">
                                    {{ number_format($totalStats['active_network_users']) }}
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-gray-100">100%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
