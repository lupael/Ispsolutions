@extends('panels.layouts.app')
@section('title', 'Yearly Operator Income Report')
@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Yearly Operator Income Report</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Annual operator earnings for {{ $year }}</p>
                </div>
                <a href="{{ route('panel.isp.yearly-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Operator Income Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Operator Income Summary</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Operator Name
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Collections
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Commissions
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Income
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($operators as $operator)
                            @php
                                $totals = $totalByOperator[$operator->id] ?? ['collections' => 0, 'commissions' => 0, 'total' => 0];
                            @endphp
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $operator->name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    {{ number_format($totals['collections'], 2) }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    {{ number_format($totals['commissions'], 2) }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($totals['total'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No operator data available for this year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown for each Operator -->
    @foreach($operators as $operator)
        @php
            $operatorMonthlyData = collect($monthlyData)->filter(function($month) use ($operator) {
                return isset($month[$operator->id]);
            });
        @endphp
        @if($operatorMonthlyData->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $operator->name }} - Monthly Breakdown</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Month
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Collections
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Commissions
                                    </th>
                                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                @endphp
                                @foreach($monthlyData as $month => $data)
                                    @if(isset($data[$operator->id]))
                                        @php
                                            $monthData = $data[$operator->id];
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $months[$month - 1] ?? $month }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                                {{ number_format($monthData['collections'] ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                                {{ number_format($monthData['commissions'] ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                                {{ number_format($monthData['total'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</div>
@endsection
