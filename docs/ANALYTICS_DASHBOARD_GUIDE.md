# Analytics Dashboard - Implementation Documentation

## Overview
The Analytics Dashboard provides comprehensive business intelligence and insights for ISP management, including revenue analytics, customer analytics, and service performance metrics with dynamic charts powered by ApexCharts.

## Features

### 1. Analytics Dashboard
- **Route**: `/panel/admin/analytics/dashboard`
- **View**: `resources/views/panels/admin/analytics/dashboard.blade.php`
- **Key Metrics**:
  - Total Revenue with growth rate
  - Total Customers (active/inactive)
  - Average Revenue Per User (ARPU)
  - Churn Rate

### 2. Dynamic Charts
- **Revenue Trend Chart**: Area chart showing daily revenue over time
- **Customer Growth Chart**: Bar chart comparing total, active, and new customers
- **Service Package Distribution**: Donut chart showing customer distribution across packages
- **Payment Method Distribution**: Pie chart showing revenue by payment method

### 3. Detailed Reports

#### Revenue Report
- **Route**: `/panel/admin/analytics/revenue-report`
- **Features**:
  - Total revenue with growth metrics
  - Daily revenue breakdown
  - Revenue by payment method
  - Comparison with previous period

#### Customer Report
- **Route**: `/panel/admin/analytics/customer-report`
- **Features**:
  - Customer status distribution
  - Customer lifetime value (CLV)
  - Customer acquisition cost (CAC)
  - Churn rate analysis

#### Service Report
- **Route**: `/panel/admin/analytics/service-report`
- **Features**:
  - Package performance metrics
  - Market share analysis
  - Revenue per package
  - Most popular packages

### 4. Data Export
- **Route**: `/panel/admin/analytics/export`
- **Format**: CSV
- **Contents**: Complete analytics data including revenue, customers, and services

### 5. API Endpoints
All API endpoints return JSON data for AJAX requests:

- `GET /panel/admin/api/analytics/revenue` - Revenue analytics
- `GET /panel/admin/api/analytics/customers` - Customer analytics
- `GET /panel/admin/api/analytics/services` - Service analytics
- `GET /panel/admin/api/analytics/growth` - Growth metrics
- `GET /panel/admin/api/analytics/performance` - Performance indicators

## Technical Implementation

### Backend Service
**File**: `app/Services/AdvancedAnalyticsService.php`

Key methods:
- `getDashboardAnalytics()` - Returns comprehensive analytics
- `getRevenueAnalytics()` - Revenue data with trends
- `getCustomerAnalytics()` - Customer metrics and segmentation
- `getServiceAnalytics()` - Service package performance
- `getPredictiveAnalytics()` - Basic forecasting

### Controller
**File**: `app/Http/Controllers/Panel/AnalyticsController.php`

Handles all analytics routes and data retrieval.

### Frontend JavaScript
**File**: `resources/js/analytics.js`

Provides reusable chart initialization and data fetching:

```javascript
// Initialize revenue chart
analyticsManager.initRevenueChart('chartElement', data);

// Initialize customer chart
analyticsManager.initCustomerChart('chartElement', data);

// Initialize donut chart
analyticsManager.initDonutChart('chartElement', data, labels);

// Fetch data via AJAX
await analyticsManager.fetchData('revenue', { start_date: '2024-01-01' });
```

### Views
Located in `resources/views/panels/admin/analytics/`:
- `dashboard.blade.php` - Main analytics dashboard
- `revenue-report.blade.php` - Detailed revenue report
- `customer-report.blade.php` - Customer analytics report
- `service-report.blade.php` - Service performance report

## Usage

### Accessing Analytics
1. Navigate to **Analytics** menu in the admin panel sidebar
2. Select desired report from the submenu:
   - Dashboard
   - Revenue Report
   - Customer Report
   - Service Report

### Filtering Data
Use the date range filter on the dashboard:
1. Select start date
2. Select end date
3. Click "Apply Filter"

### Exporting Data
Click the "Export Report" button on the dashboard to download analytics data as CSV.

### Refreshing Data
Click the "Refresh Data" button to reload analytics with current data.

## Chart Types

### 1. Area Chart (Revenue Trend)
- Smooth gradient visualization
- Hover tooltips with detailed values
- Date-based x-axis
- Currency-formatted y-axis

### 2. Bar Chart (Customer Growth)
- Grouped bars for comparison
- Color-coded categories
- Responsive design

### 3. Donut Chart (Package Distribution)
- Percentage-based segmentation
- Legend at bottom
- Interactive hover states

### 4. Pie Chart (Payment Methods)
- Simple distribution view
- Color-coded segments
- Revenue-based sizing

## Predictive Analytics

### Basic Forecasting
The system provides simple predictive analytics based on historical data:

- **Predicted Revenue**: Based on 3-month average with 5% growth assumption
- **Expected New Customers**: Based on last month's trend with 10% growth
- **Predicted Churn**: Based on recent churn rate with 5% improvement assumption

These predictions are displayed on the main dashboard when available.

## Performance Considerations

### Data Caching
Consider implementing caching for analytics data:

```php
Cache::remember('analytics_dashboard_' . $tenantId, 3600, function() {
    return $this->analyticsService->getDashboardAnalytics();
});
```

### Query Optimization
- Analytics queries use database aggregations for performance
- Indexes should be added on frequently queried columns:
  - `payments.payment_date`
  - `payments.tenant_id`
  - `network_users.created_at`
  - `network_users.tenant_id`

### Chart Performance
- Charts are rendered client-side using ApexCharts
- Large datasets are automatically sampled
- Responsive design ensures mobile compatibility

## Customization

### Adding New Metrics
1. Add calculation logic to `AdvancedAnalyticsService`
2. Update controller to pass data to view
3. Add display in blade template
4. Update chart initialization if needed

### Custom Chart Types
Use the `AnalyticsManager` class to create custom charts:

```javascript
const chart = new ApexCharts(element, {
    series: [...],
    chart: { type: 'line' },
    // ... custom options
});
chart.render();
```

### Styling Charts
Charts inherit theme colors but can be customized:

```javascript
colors: ['#10B981', '#3B82F6', '#F59E0B']
```

## Security

### Authorization
- Only users with `admin` role can access analytics
- Middleware: `auth`, `role:admin`
- All data is filtered by `tenant_id` for multi-tenancy

### Data Validation
- Date inputs are validated and sanitized
- API endpoints require authentication
- CSRF protection on all POST requests

## Testing

Run analytics tests:
```bash
php artisan test --filter=AnalyticsDashboardTest
```

The test suite includes:
- Route accessibility tests
- Data rendering tests
- API endpoint tests
- Authorization tests
- Export functionality tests

## Future Enhancements

Potential improvements for the analytics system:

1. **Real-time Updates**: WebSocket integration for live data
2. **Custom Dashboards**: User-configurable widgets
3. **Advanced ML Predictions**: Machine learning for churn prediction
4. **Comparative Analytics**: Multi-period comparisons
5. **Scheduled Reports**: Email reports on schedule
6. **Data Drill-down**: Click charts to see detailed data
7. **Custom Metrics**: User-defined KPIs
8. **Mobile App**: Native mobile analytics app

## Troubleshooting

### Charts Not Rendering
1. Check browser console for JavaScript errors
2. Verify ApexCharts is loaded: `typeof ApexCharts`
3. Ensure data format is correct in view source

### Data Not Loading
1. Check database connections
2. Verify tenant_id filtering
3. Review Laravel logs: `storage/logs/laravel.log`

### Export Not Working
1. Check write permissions on storage directory
2. Verify CSV generation in controller
3. Check browser download settings

## Dependencies

- **Backend**: Laravel 12.x, PHP 8.2+
- **Frontend**: ApexCharts 3.45+, Alpine.js 3.13+, Tailwind CSS 4.x
- **Database**: MySQL 8.0+

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review browser console for frontend errors
3. Verify database queries with Laravel Debugbar
4. Consult the main project README for general setup

---

**Last Updated**: 2026-01-20  
**Version**: 1.0.0
