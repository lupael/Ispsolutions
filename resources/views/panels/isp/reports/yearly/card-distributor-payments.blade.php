@extends('panels.layouts.app')
@section('title', 'Yearly Card Distributor Payments Report')
@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Yearly Card Distributor Payments</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Annual payment tracking for {{ $year }}</p>
                </div>
                <a href="{{ route('panel.isp.yearly-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Grand Total ({{ $year }})</h2>
            <p class="mt-4 text-3xl font-bold text-blue-600 dark:text-blue-400">
                {{ number_format($grandTotal, 2) }}
            </p>
        </div>
    </div>

    <!-- Distributor Summary -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Payment Summary by Distributor</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Distributor Name
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Payments
                            </th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                % of Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($distributors as $distributor)
                            @php
                                $total = $totalByDistributor[$distributor->id] ?? 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $distributor->name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                    {{ number_format($total, 2) }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-400">
                                    @if($grandTotal > 0)
                                        {{ number_format(($total / $grandTotal) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No card distributor data available for this year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Monthly Breakdown</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Month
                            </th>
                            @foreach($distributors as $distributor)
                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    {{ Str::limit($distributor->name, 12) }}
                                </th>
                            @endforeach
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        @endphp
                        @foreach($monthlyData as $month => $data)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $months[$month - 1] ?? $month }}
                                </td>
                                @php $monthTotal = 0; @endphp
                                @foreach($distributors as $distributor)
                                    @php
                                        $amount = $data[$distributor->id] ?? 0;
                                        $monthTotal += $amount;
                                    @endphp
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                                        {{ number_format($amount, 2) }}
                                    </td>
                                @endforeach
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                    {{ number_format($monthTotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
