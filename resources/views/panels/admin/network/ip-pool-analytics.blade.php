@extends('panels.layouts.app')

@section('title', 'IP Pool Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">IP Pool Usage Analytics</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Comprehensive analytics and reporting for IP address pool utilization</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Report
                    </button>
                    <a href="{{ route('panel.admin.network.ipv4-pools') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Pools
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-info-box 
            title="Total IP Addresses" 
            :value="number_format($analytics['total_ips'] ?? 0)"
            icon="network"
            color="blue"
            subtitle="Across all pools"
        />
        
        <x-info-box 
            title="Allocated IPs" 
            :value="number_format($analytics['allocated_ips'] ?? 0)"
            icon="check"
            color="green"
            :subtitle="number_format($analytics['allocation_percent'] ?? 0, 1) . '% of total'"
        />
        
        <x-info-box 
            title="Available IPs" 
            :value="number_format($analytics['available_ips'] ?? 0)"
            icon="users"
            color="purple"
            :subtitle="number_format($analytics['available_percent'] ?? 0, 1) . '% of total'"
        />
        
        <x-info-box 
            title="Total Pools" 
            :value="$analytics['total_pools'] ?? 0"
            icon="chart"
            color="indigo"
            subtitle="Active IP pools"
        />
    </div>

    <!-- Pool Utilization Overview -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Pool Utilization Overview</h2>
            
            <div class="space-y-4">
                @forelse($poolStats ?? [] as $pool)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $pool['name'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $pool['description'] ?? 'No description' }}</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ number_format($pool['utilization_percent'], 1) }}%
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Utilization</div>
                            </div>
                        </div>

                        <!-- Pool Details Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">IP Range</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $pool['start_ip'] }} - {{ $pool['end_ip'] }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Gateway</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $pool['gateway'] ?? 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Total IPs</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ number_format($pool['total_ips']) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Available</div>
                                <div class="text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ number_format($pool['available_ips']) }}
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <x-progress-bar 
                            :current="$pool['allocated_ips']" 
                            :total="$pool['total_ips']" 
                            height="h-6"
                            :showLabel="true"
                            :showPercentage="true"
                        />

                        <!-- Status Badge -->
                        <div class="mt-3">
                            @php
                                $statusBadge = match(true) {
                                    $pool['utilization_percent'] >= 90 => ['bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100', 'Critical - Immediate action required'],
                                    $pool['utilization_percent'] >= 70 => ['bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100', 'Warning - Consider expanding pool'],
                                    default => ['bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100', 'Healthy - Adequate capacity']
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusBadge[0] }}">
                                {{ $statusBadge[1] }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>No IP pools configured yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Utilization Trends -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Allocation by Pool Type -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Allocation by Pool Type</h3>
                
                <div class="space-y-3">
                    @foreach($analytics['by_type'] ?? [] as $type => $data)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700 dark:text-gray-300 font-medium">{{ ucfirst($type) }}</span>
                                <span class="text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ number_format($data['allocated']) }} / {{ number_format($data['total']) }}
                                </span>
                            </div>
                            <x-progress-bar 
                                :current="$data['allocated']" 
                                :total="$data['total']" 
                                height="h-4"
                                :showLabel="false"
                                :showPercentage="true"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Utilized Pools -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Top Utilized Pools</h3>
                
                <div class="space-y-3">
                    @foreach(($analytics['top_utilized'] ?? []) as $pool)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $pool['name'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($pool['allocated']) }} / {{ number_format($pool['total']) }} IPs
                                </div>
                            </div>
                            <div class="text-right">
                                @php
                                    $percent = $pool['utilization'];
                                    $color = $percent >= 90 ? 'text-red-600 dark:text-red-400' : ($percent >= 70 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400');
                                @endphp
                                <div class="text-2xl font-bold {{ $color }}">
                                    {{ number_format($percent, 1) }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Allocations -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Recent IP Allocations</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pool</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentAllocations ?? [] as $allocation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-gray-100">
                                    {{ $allocation['ip_address'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $allocation['pool_name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $allocation['assigned_to'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ isset($allocation['allocated_at']) && $allocation['allocated_at'] ? $allocation['allocated_at']->format('M d, Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        Active
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No recent allocations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Export Analytics</h3>
            
            <!-- Note: Export routes implemented in routes/web.php:
                 Route::get('panel/admin/network/ip-analytics/export', [AdminController::class, 'exportIpAnalytics'])
                      ->name('panel.admin.network.ip-analytics.export');
            -->
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.admin.network.ip-analytics.export', ['format' => 'pdf']) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export as PDF
                </a>
                
                <a href="{{ route('panel.admin.network.ip-analytics.export', ['format' => 'excel']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export as Excel
                </a>
                
                <a href="{{ route('panel.admin.network.ip-analytics.export', ['format' => 'csv']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export as CSV
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
