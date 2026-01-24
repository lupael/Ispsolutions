@extends('layouts.panel')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Analytics Dashboard</h3>
            <button class="btn btn-sm btn-primary" onclick="refreshAllWidgets()">
                <i class="fas fa-sync-alt me-1"></i> Refresh All
            </button>
        </div>
        <div class="card-body">
            <!-- Date Range Filter -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">Apply</button>
                    </div>
                </div>
            </form>

            <!-- Widgets Section -->
            @if(isset($widgets))
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        @include('panels.shared.widgets.suspension-forecast')
                    </div>
                    <div class="col-md-4 mb-3">
                        @include('panels.shared.widgets.collection-target')
                    </div>
                    <div class="col-md-4 mb-3">
                        @include('panels.shared.widgets.sms-usage')
                    </div>
                </div>
            @endif

            <!-- Revenue Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Revenue</h5>
                            <h2>{{ number_format($analytics['revenue_analytics']['total_revenue'] ?? 0, 2) }} BDT</h2>
                            <small>Period: {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Total Customers</h5>
                            <h2>{{ $analytics['customer_analytics']['total_customers'] ?? 0 }}</h2>
                            <small>Active: {{ $analytics['customer_analytics']['active_customers'] ?? 0 }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Avg Daily Revenue</h5>
                            <h2>{{ number_format($analytics['revenue_analytics']['average_daily_revenue'] ?? 0, 2) }} BDT</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>Growth Rate</h5>
                            <h2>{{ number_format($analytics['revenue_analytics']['growth_rate'] ?? 0, 2) }}%</h2>
                            <small>vs Previous Period</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue by Method -->
            @if(isset($analytics['revenue_analytics']['revenue_by_method']) && $analytics['revenue_analytics']['revenue_by_method']->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Revenue by Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th>Revenue</th>
                                    <th>Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['revenue_analytics']['revenue_by_method'] as $method)
                                    <tr>
                                        <td>{{ ucfirst($method->payment_method) }}</td>
                                        <td>{{ number_format($method->revenue, 2) }} BDT</td>
                                        <td>{{ $method->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Quick Links -->
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <a href="{{ route('analytics.revenue') }}" class="btn btn-outline-primary">Detailed Revenue Report</a>
                        <a href="{{ route('analytics.customers') }}" class="btn btn-outline-success">Customer Analytics</a>
                    </div>
                </div>
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
            cardBody.innerHTML = '<div class="alert alert-danger">Failed to refresh widget. Please try again.</div>';
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
