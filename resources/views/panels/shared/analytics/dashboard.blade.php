@extends('layouts.panel')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="w-full px-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Analytics Dashboard</h3>
            <button class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="refreshAllWidgets()">
                <i class="fas fa-sync-alt mr-1"></i> Refresh All
            </button>
        </div>
        <div class="p-6">
            <!-- Date Range Filter -->
            <form method="GET" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Apply</button>
                    </div>
                </div>
            </form>

            <!-- Widgets Section -->
            @if(isset($widgets))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        @include('panels.shared.widgets.suspension-forecast')
                    </div>
                    <div>
                        @include('panels.shared.widgets.collection-target')
                    </div>
                    <div>
                        @include('panels.shared.widgets.sms-usage')
                    </div>
                </div>
            @endif

            <!-- Revenue Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-blue-600 text-white rounded-lg shadow">
                    <div class="p-6">
                        <h5 class="text-lg font-semibold">Total Revenue</h5>
                        <h2 class="text-3xl font-bold mt-2">{{ number_format($analytics['revenue_analytics']['total_revenue'] ?? 0, 2) }} BDT</h2>
                        <small class="text-blue-100">Period: {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</small>
                    </div>
                </div>
                <div class="bg-green-600 text-white rounded-lg shadow">
                    <div class="p-6">
                        <h5 class="text-lg font-semibold">Total Customers</h5>
                        <h2 class="text-3xl font-bold mt-2">{{ $analytics['customer_analytics']['total_customers'] ?? 0 }}</h2>
                        <small class="text-green-100">Active: {{ $analytics['customer_analytics']['active_customers'] ?? 0 }}</small>
                    </div>
                </div>
                <div class="bg-cyan-600 text-white rounded-lg shadow">
                    <div class="p-6">
                        <h5 class="text-lg font-semibold">Avg Daily Revenue</h5>
                        <h2 class="text-3xl font-bold mt-2">{{ number_format($analytics['revenue_analytics']['average_daily_revenue'] ?? 0, 2) }} BDT</h2>
                    </div>
                </div>
                <div class="bg-yellow-600 text-white rounded-lg shadow">
                    <div class="p-6">
                        <h5 class="text-lg font-semibold">Growth Rate</h5>
                        <h2 class="text-3xl font-bold mt-2">{{ number_format($analytics['revenue_analytics']['growth_rate'] ?? 0, 2) }}%</h2>
                        <small class="text-yellow-100">vs Previous Period</small>
                    </div>
                </div>
            </div>

            <!-- Revenue by Method -->
            @if(isset($analytics['revenue_analytics']['revenue_by_method']) && $analytics['revenue_analytics']['revenue_by_method']->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue by Payment Method</h5>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transactions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($analytics['revenue_analytics']['revenue_by_method'] as $method)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($method->payment_method) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($method->revenue, 2) }} BDT</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $method->count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Links -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('analytics.revenue') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-blue-600 dark:border-blue-500 rounded-md font-semibold text-xs text-blue-600 dark:text-blue-400 uppercase tracking-widest hover:bg-blue-600 hover:text-white focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">Detailed Revenue Report</a>
                <a href="{{ route('analytics.customers') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-green-600 dark:border-green-500 rounded-md font-semibold text-xs text-green-600 dark:text-green-400 uppercase tracking-widest hover:bg-green-600 hover:text-white focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">Customer Analytics</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
function updateWidgetContent(widgetName, data) {
    // This would ideally re-render the widget with new data
    // For now, we'll reload the page as a simple solution
    // TODO: Implement dynamic widget content update without page reload
    window.location.href = window.location.pathname + '?refresh=1';
}

function refreshWidget(widgetName) {
    const widgetElement = document.getElementById(`${widgetName}-widget`);
    if (!widgetElement) return;
    
    // Add loading state
    const originalContent = widgetElement.innerHTML;
    const cardBody = widgetElement.querySelector('.card-body');
    if (cardBody) {
        cardBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-2"></i><p class="mt-2">Refreshing...</p></div>';
    }
    
    fetch(`/api/v1/widgets/refresh`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ widgets: [widgetName] })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Simple approach: reload page with refresh flag
            // This ensures widget data is freshly rendered by the server
            updateWidgetContent(widgetName, data.data[widgetName]);
        } else {
            throw new Error('Failed to refresh widget');
        }
    })
    .catch(error => {
        console.error('Error refreshing widget:', error);
        if (cardBody) {
            cardBody.innerHTML = '<div class="p-4 rounded-md mb-4 bg-red-50 border border-red-200 text-red-800">Failed to refresh widget. Please try again.</div>';
        }
        // Restore original content after 2 seconds
        setTimeout(() => {
            widgetElement.innerHTML = originalContent;
        }, 2000);
    });
}

function refreshAllWidgets() {
    // Show loading state
    const widgets = ['suspension_forecast', 'collection_target', 'sms_usage'];
    widgets.forEach(widgetName => {
        const widgetElement = document.getElementById(`${widgetName}-widget`);
        if (widgetElement) {
            const cardBody = widgetElement.querySelector('.card-body');
            if (cardBody) {
                cardBody.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-2"></i><p class="mt-2">Refreshing...</p></div>';
            }
        }
    });
    
    fetch(`/api/v1/widgets/refresh`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Simple approach: reload page with refresh flag
            // This ensures all widgets are freshly rendered by the server
            window.location.href = window.location.pathname + '?refresh=1';
        } else {
            throw new Error('Failed to refresh widgets');
        }
    })
    .catch(error => {
        console.error('Error refreshing widgets:', error);
        alert('Failed to refresh widgets. Please try again.');
        window.location.reload();
    });
}
</script>
@endpush
@endsection
