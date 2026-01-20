/**
 * Analytics Module
 * Handles chart initialization and data fetching for analytics dashboards
 * 
 * Note: ApexCharts is loaded via CDN in the view templates
 */

class AnalyticsManager {
    constructor() {
        this.charts = {};
        this.baseUrl = '/panel/admin/api/analytics';
    }

    /**
     * Initialize revenue trend chart
     */
    initRevenueChart(elementId, data, options = {}) {
        // Check if ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library not loaded');
            return null;
        }
        
        const defaultOptions = {
            series: [{
                name: 'Revenue',
                data: data.map(item => ({
                    x: new Date(item.date).getTime(),
                    y: parseFloat(item.revenue)
                }))
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            colors: ['#10B981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                }
            },
            xaxis: {
                type: 'datetime',
                labels: { format: 'MMM dd' }
            },
            yaxis: {
                labels: {
                    formatter: (value) => '৳' + value.toFixed(2)
                }
            },
            tooltip: {
                x: { format: 'dd MMM yyyy' },
                y: {
                    formatter: (value) => '৳' + value.toFixed(2)
                }
            }
        };

        const chartOptions = { ...defaultOptions, ...options };
        const chart = new ApexCharts(document.querySelector(`#${elementId}`), chartOptions);
        chart.render();
        
        this.charts[elementId] = chart;
        return chart;
    }

    /**
     * Initialize customer growth chart
     */
    initCustomerChart(elementId, data, options = {}) {
        // Check if ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library not loaded');
            return null;
        }
        
        const defaultOptions = {
            series: [{
                name: 'Total Customers',
                data: [data.total_customers]
            }, {
                name: 'Active Customers',
                data: [data.active_customers]
            }, {
                name: 'New Customers',
                data: [data.new_customers]
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 5
                }
            },
            colors: ['#3B82F6', '#10B981', '#F59E0B'],
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: ['Customers']
            },
            yaxis: {
                title: { text: 'Count' }
            },
            fill: { opacity: 1 },
            tooltip: {
                y: {
                    formatter: (value) => value + ' customers'
                }
            }
        };

        const chartOptions = { ...defaultOptions, ...options };
        const chart = new ApexCharts(document.querySelector(`#${elementId}`), chartOptions);
        chart.render();
        
        this.charts[elementId] = chart;
        return chart;
    }

    /**
     * Initialize donut/pie chart
     */
    initDonutChart(elementId, data, labels, options = {}) {
        // Check if ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library not loaded');
            return null;
        }
        
        const defaultOptions = {
            series: data,
            chart: {
                type: 'donut',
                height: 350
            },
            labels: labels,
            colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'],
            legend: { position: 'bottom' },
            plotOptions: {
                pie: {
                    donut: { size: '65%' }
                }
            }
        };

        const chartOptions = { ...defaultOptions, ...options };
        const chart = new ApexCharts(document.querySelector(`#${elementId}`), chartOptions);
        chart.render();
        
        this.charts[elementId] = chart;
        return chart;
    }

    /**
     * Initialize pie chart
     */
    initPieChart(elementId, data, labels, options = {}) {
        // Check if ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts library not loaded');
            return null;
        }
        
        const defaultOptions = {
            series: data,
            chart: {
                type: 'pie',
                height: 350
            },
            labels: labels,
            colors: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444'],
            legend: { position: 'bottom' }
        };

        const chartOptions = { ...defaultOptions, ...options };
        const chart = new ApexCharts(document.querySelector(`#${elementId}`), chartOptions);
        chart.render();
        
        this.charts[elementId] = chart;
        return chart;
    }

    /**
     * Fetch analytics data via AJAX
     */
    async fetchData(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}/${endpoint}${queryString ? '?' + queryString : ''}`;
        
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error fetching analytics data:', error);
            
            // Show user-facing error notification
            this.showErrorNotification('Failed to load analytics data. Please try again.');
            
            throw error;
        }
    }

    /**
     * Show error notification to user
     */
    showErrorNotification(message) {
        // Create notification element if it doesn't exist
        let notification = document.getElementById('analytics-error-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'analytics-error-notification';
            notification.className = 'fixed top-4 right-4 z-50 max-w-md';
            document.body.appendChild(notification);
        }
        
        // Set notification content with Tailwind classes
        notification.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-lg" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> ${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Close</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </button>
            </div>
        `;
        
        // Auto-remove notification after 5 seconds
        setTimeout(() => {
            if (notification && notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Update chart data
     */
    updateChart(chartId, newData, newLabels = null) {
        const chart = this.charts[chartId];
        if (chart) {
            if (newLabels) {
                chart.updateOptions({
                    labels: newLabels
                });
            }
            chart.updateSeries(newData);
        }
    }

    /**
     * Destroy chart
     */
    destroyChart(chartId) {
        const chart = this.charts[chartId];
        if (chart) {
            chart.destroy();
            delete this.charts[chartId];
        }
    }

    /**
     * Destroy all charts
     */
    destroyAll() {
        Object.keys(this.charts).forEach(chartId => {
            this.destroyChart(chartId);
        });
    }
}

// Export for use in other modules
export default AnalyticsManager;

// Also make it available globally for inline scripts
window.AnalyticsManager = AnalyticsManager;
