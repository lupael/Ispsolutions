@props(['ispMRC', 'clientsMRC', 'operatorClientsMRC', 'mrcComparison'])

@php
    $months = $mrcComparison->pluck('month')->toArray();
    $ispMRCData = $mrcComparison->pluck('isp_mrc')->toArray();
    $clientsMRCData = $mrcComparison->pluck('clients_mrc')->toArray();
    $operatorMRCData = $mrcComparison->pluck('operator_clients_mrc')->toArray();
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Revenue - Monthly Recurring Charge (MRC)</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Last 3 Months Comparison</span>
    </div>
    
    <!-- MRC Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- ISP's MRC -->
        <div class="space-y-3">
            <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">ISP's MRC</h4>
            <div class="space-y-2">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-blue-700 dark:text-blue-300 mb-0.5">Current MRC</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($ispMRC['current_mrc'], 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">This Month Avg. MRC</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($ispMRC['this_month_avg_mrc'], 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">Last Month Avg. MRC</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($ispMRC['last_month_avg_mrc'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Client's MRC -->
        <div class="space-y-3">
            <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Clients MRC</h4>
            <div class="space-y-2">
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-green-700 dark:text-green-300 mb-0.5">Current MRC</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ number_format($clientsMRC['current_mrc'], 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">This Month Avg. MRC</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($clientsMRC['this_month_avg_mrc'], 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">Last Month Avg. MRC</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($clientsMRC['last_month_avg_mrc'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Client's Of Operator MRC -->
        <div class="space-y-3">
            <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2">Clients of Operator MRC</h4>
            <div class="space-y-2">
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-purple-700 dark:text-purple-300 mb-0.5">Current MRC</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($operatorClientsMRC['current_mrc'], 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">This Month Avg. MRC</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($operatorClientsMRC['this_month_avg_mrc'], 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-0.5">Last Month Avg. MRC</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($operatorClientsMRC['last_month_avg_mrc'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-Month MRC Comparison Chart -->
    <div class="mt-6" aria-labelledby="mrc-comparison-heading" aria-describedby="mrc-comparison-summary">
        <h4 id="mrc-comparison-heading" class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">3-Month MRC Comparison</h4>
        <p id="mrc-comparison-summary" class="sr-only">
            Bar chart comparing ISP MRC, Clients MRC, and Clients of Operator MRC over the last three months
            ({{ implode(', ', $months) }}). The chart shows how these monthly recurring charges change over time for each group.
        </p>
        <div
            id="mrc-comparison-chart"
            role="img"
            aria-label="3-month bar chart showing the trend of ISP, Clients, and Operator clients monthly recurring charges over {{ implode(', ', $months) }}."
            style="min-height: 350px;"
        ></div>
    </div>
    
    <script>
        (function() {
            // Initialize chart immediately if DOM is ready, or wait for DOMContentLoaded
            function initMRCChart() {
                if (typeof ApexCharts === 'undefined') {
                    console.error('ApexCharts is not loaded');
                    return;
                }
                
                const chartElement = document.querySelector('#mrc-comparison-chart');
                if (!chartElement) return;
                
                // Get currency symbol from config (defaults to $)
                const currencySymbol = '{{ config("app.currency", "$") }}';
                
                const options = {
                    series: [
                        {
                            name: "ISP's MRC",
                            data: @json($ispMRCData)
                        },
                        {
                            name: "Clients MRC",
                            data: @json($clientsMRCData)
                        },
                        {
                            name: "Operator Clients MRC",
                            data: @json($operatorMRCData)
                        }
                    ],
                    chart: {
                        type: 'bar',
                        height: 350,
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
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '70%',
                            borderRadius: 6,
                            dataLabels: {
                                position: 'top',
                            },
                        }
                    },
                    colors: ['#3b82f6', '#10b981', '#8b5cf6'],
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val > 0 ? val.toFixed(0) : '';
                        },
                        offsetY: -20,
                        style: {
                            fontSize: '11px',
                            colors: ['#304758']
                        }
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
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
                        title: {
                            text: 'MRC Amount',
                            style: {
                                color: '#9ca3af'
                            }
                        },
                        labels: {
                            style: {
                                colors: '#9ca3af',
                                fontSize: '12px'
                            },
                            formatter: function(value) {
                                return currencySymbol + value.toFixed(0);
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
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
                                return currencySymbol + value.toFixed(2);
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center',
                        fontSize: '13px',
                        labels: {
                            colors: '#9ca3af'
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
                
                // Handle dark mode changes with cleanup
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
                    
                    // Disconnect observer on page unload to prevent memory leaks
                    window.addEventListener('beforeunload', function() {
                        observer.disconnect();
                    });
                }
            }
            
            // Check if DOM is already loaded, otherwise wait for DOMContentLoaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initMRCChart);
            } else {
                initMRCChart();
            }
        })();
    </script>
</div>
