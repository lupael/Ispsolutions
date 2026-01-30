@props(['customerGrowth'])

@php
    $months = $customerGrowth->pluck('month')->toArray();
    $customers = $customerGrowth->pluck('customers')->toArray();
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Customer Growth</h3>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Last 6 Months</span>
        </div>
    </div>
    
    <div id="customer-growth-chart" style="min-height: 300px;"></div>
    
    <script nonce="{{ $cspNonce ?? '' }}">
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded');
                return;
            }
            
            const chartElement = document.querySelector('#customer-growth-chart');
            if (!chartElement) return;
            
            const options = {
                series: [{
                    name: 'Total Customers',
                    data: @json($customers)
                }],
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                colors: ['#10b981'],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        dataLabels: {
                            position: 'top'
                        },
                        columnWidth: '60%'
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ['#9ca3af']
                    }
                },
                xaxis: {
                    categories: @json($months),
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '12px'
                        }
                    }
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value + ' customers';
                        }
                    }
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                }
            };
            
            const chart = new ApexCharts(chartElement, options);
            chart.render().catch(error => {
                console.error('Error rendering chart:', error);
            });
            
            // Handle dark mode changes - only if chart was successfully created
            if (chart) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            const isDark = document.documentElement.classList.contains('dark');
                            chart.updateOptions({
                                theme: {
                                    mode: isDark ? 'dark' : 'light'
                                }
                            });
                        }
                    });
                });
                
                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        });
    </script>
</div>
