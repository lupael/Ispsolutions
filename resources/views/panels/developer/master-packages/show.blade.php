@extends('panels.layouts.app')

@section('title', 'Master Package Details')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $masterPackage->name }}</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Master Package Details</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('panel.developer.master-packages.edit', $masterPackage) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Edit
                    </a>
                    <a href="{{ route('panel.developer.master-packages.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Operators</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['operator_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Customers</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['customer_count'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">${{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
    </div>

    <!-- Package Details -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Package Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Base Price</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">${{ number_format($masterPackage->base_price, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Download Speed</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $masterPackage->speed_download ? number_format($masterPackage->speed_download / 1024, 2) . ' Mbps' : 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Upload Speed</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $masterPackage->speed_upload ? number_format($masterPackage->speed_upload / 1024, 2) . ' Mbps' : 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Validity</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $masterPackage->validity_days }} days</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                    <div>
                        @if($masterPackage->status === 'active')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Visibility</div>
                    <div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $masterPackage->visibility === 'public' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($masterPackage->visibility) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operator Rates -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Operator Rates</h2>
                <a href="{{ route('panel.developer.master-packages.assign', $masterPackage) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Assign to Operator
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Operator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Operator Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Margin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($masterPackage->operatorRates as $rate)
                            @php
                                $margin = $masterPackage->base_price > 0 ? (($rate->operator_price - $masterPackage->base_price) / $masterPackage->base_price) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $rate->operator->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${{ number_format($rate->operator_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $margin < 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                        {{ number_format($margin, 2) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($rate->status === 'active')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('panel.developer.master-packages.remove-operator', [$masterPackage, $rate]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Remove this operator assignment?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No operators assigned yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
