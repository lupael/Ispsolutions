@extends('layouts.admin')

@section('title', 'Search Results')

@section('content')
<div class="container mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Search Results</h1>
        @if($query)
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Results for: <span class="font-semibold">"{{ $query }}"</span>
            </p>
        @else
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Enter a search query to find customers and invoices
            </p>
        @endif
    </div>

    <!-- Search Form -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('panel.search') }}" class="flex gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="bi bi-search text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            name="query" 
                            value="{{ $query }}"
                            placeholder="Search by name, email, mobile, username, or invoice number..." 
                            class="pl-10 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            autofocus
                        >
                    </div>
                </div>
                <button 
                    type="submit" 
                    class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <i class="bi bi-search mr-2"></i>
                    Search
                </button>
                @if($query)
                    <a 
                        href="{{ route('panel.search') }}" 
                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <i class="bi bi-x-lg mr-2"></i>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    @if($query)
        <!-- Customers Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    <i class="bi bi-people-fill mr-2"></i>
                    Customers ({{ $customers->count() }})
                </h2>
                
                @if($customers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mobile</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    @if(in_array(auth()->user()->operator_level, [0, 10]))
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($customers as $customer)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $customer->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $customer->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $customer->username ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $customer->mobile ?? $customer->phone ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $customer->currentPackage?->name ?? 'No Package' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($customer->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                        @if(in_array(auth()->user()->operator_level, [0, 10]))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $customer->tenant?->company_name ?? 'N/A' }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @php
                                                $userRole = auth()->user()->roles->first()?->slug ?? '';
                                                $viewRoute = null;
                                                
                                                if ($userRole === 'admin') {
                                                    $viewRoute = route('panel.admin.customers.show', $customer->id);
                                                } elseif ($userRole === 'operator') {
                                                    $viewRoute = route('panel.operator.customers.show', $customer->id);
                                                } elseif ($userRole === 'manager') {
                                                    $viewRoute = route('panel.manager.customers.show', $customer->id);
                                                }
                                            @endphp
                                            
                                            @if($viewRoute)
                                                <a href="{{ $viewRoute }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">
                        <i class="bi bi-inbox text-3xl"></i><br>
                        No customers found matching your search.
                    </p>
                @endif
            </div>
        </div>

        <!-- Invoices Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    <i class="bi bi-receipt mr-2"></i>
                    Invoices ({{ $invoices->count() }})
                </h2>
                
                @if($invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email / Mobile</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Package</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $invoice->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $invoice->user?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $invoice->user?->email ?? 'N/A' }}<br>
                                            <span class="text-xs">{{ $invoice->user?->mobile ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $invoice->package?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-medium">
                                            {{ number_format($invoice->total_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClasses = [
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'overdue' => 'bg-red-100 text-red-800',
                                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                                    'draft' => 'bg-blue-100 text-blue-800',
                                                ];
                                                $statusClass = $statusClasses[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @php
                                                $userRole = auth()->user()->roles->first()?->slug ?? '';
                                                $viewRoute = null;
                                                
                                                if (in_array($userRole, ['admin', 'manager', 'accountant'])) {
                                                    $viewRoute = route('panel.admin.invoices.show', $invoice->id);
                                                } elseif ($userRole === 'operator') {
                                                    $viewRoute = route('panel.operator.invoices.show', $invoice->id);
                                                }
                                            @endphp
                                            
                                            @if($viewRoute)
                                                <a href="{{ $viewRoute }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">
                        <i class="bi bi-inbox text-3xl"></i><br>
                        No invoices found matching your search.
                    </p>
                @endif
            </div>
        </div>

        @if($customers->count() === 0 && $invoices->count() === 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <i class="bi bi-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Results Found</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Try adjusting your search terms or search for different criteria.
                    </p>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
