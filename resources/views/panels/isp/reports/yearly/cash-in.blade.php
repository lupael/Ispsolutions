@extends('panels.layouts.app')
@section('title', 'Yearly Cash In Report')
@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Yearly Cash In Report</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Annual income report for {{ $year }}</p>
                </div>
                <a href="{{ route('panel.isp.yearly-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total Cash In ({{ $year }})</h2>
                <p class="mt-4 text-3xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format($yearlyTotal, 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Average Monthly Income</h2>
                <p class="mt-4 text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($averageMonthly, 2) }}
                </p>
            </div>
        </div>
    </div>

    <!-- Monthly breakdown -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Monthly Breakdown</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Income totals by month for {{ $year }}.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Month
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Income
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payment Count
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                        @endphp
                        @forelse($monthlyIncome as $month => $data)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $months[$month - 1] ?? $month }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($data['total'] ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ number_format($data['count'] ?? 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No monthly income data available for this year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Source breakdown -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Income by Payment Method</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Income breakdown by payment source for {{ $year }}.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Payment Method
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Income
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($sourceBreakdown as $method => $monthlyData)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ucfirst($method) }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    {{ number_format(array_sum($monthlyData), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No source breakdown data available for this year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
