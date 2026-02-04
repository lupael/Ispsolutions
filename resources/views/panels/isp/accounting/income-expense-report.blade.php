@extends('panels.layouts.app')

@section('title', 'Income vs Expense Report')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Income vs Expense Report</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Compare income and expenses with detailed analytics</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('panel.isp.reports.income-expense.export', ['format' => 'excel']) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>
                    <a href="{{ route('panel.isp.reports.income-expense.export', ['format' => 'pdf']) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Income</dt>
                            <dd class="text-2xl font-semibold text-green-600">৳ 450,000</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Expense</dt>
                            <dd class="text-2xl font-semibold text-red-600">৳ 165,000</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Net Profit</dt>
                            <dd class="text-2xl font-semibold text-blue-600">৳ 285,000</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Profit Margin</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100">63.3%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period</label>
                    <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option>This Month</option>
                        <option>Last Month</option>
                        <option>This Quarter</option>
                        <option>This Year</option>
                        <option>Custom Range</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date From</label>
                    <input type="date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date To</label>
                    <input type="date" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option>All Categories</option>
                        <option>Operational</option>
                        <option>Marketing</option>
                        <option>Administrative</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Generate Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Placeholder -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Monthly Comparison</h3>
            <div class="h-64 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Chart will be displayed here</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Breakdown -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <!-- Income Breakdown -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Income Breakdown</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Customer Payments</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 380,000</div>
                            <div class="text-xs text-gray-500">84.4%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Installation Fees</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 45,000</div>
                            <div class="text-xs text-gray-500">10%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-300 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Late Fees</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 15,000</div>
                            <div class="text-xs text-gray-500">3.3%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-200 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Other Income</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 10,000</div>
                            <div class="text-xs text-gray-500">2.2%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Expense Breakdown</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">ISP Bandwidth</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 85,000</div>
                            <div class="text-xs text-gray-500">51.5%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-400 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Staff Salaries</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 45,000</div>
                            <div class="text-xs text-gray-500">27.3%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-300 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Office Rent</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 20,000</div>
                            <div class="text-xs text-gray-500">12.1%</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-200 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-900 dark:text-gray-100">Utilities & Others</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">৳ 15,000</div>
                            <div class="text-xs text-gray-500">9.1%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Comparison Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Monthly Comparison</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Income</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expense</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Profit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">January 2024</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">৳ 450,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">৳ 165,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-blue-600">৳ 285,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">63.3%</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">December 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">৳ 420,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">৳ 155,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-blue-600">৳ 265,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">63.1%</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">November 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">৳ 395,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">৳ 148,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-blue-600">৳ 247,000</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">62.5%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
