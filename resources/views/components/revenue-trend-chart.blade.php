@props(['revenueTrend'])

@php
    $months = $revenueTrend->pluck('month')->toArray();
    $revenues = $revenueTrend->pluck('revenue')->toArray();
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue Trend</h3>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Last 6 Months</span>
        </div>
    </div>
    
    <div id="revenue-trend-chart" style="min-height: 300px;"></div>
    
    <script nonce="{{ $cspNonce ?? '' }}">
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded');
                return;
            }
            
            const chartElement = document.querySelector('#revenue-trend-chart');
            if (!chartElement) return;
            
            const options = {
                series: [{
                    name: 'Revenue',
                    data: @json($revenues)
                }],
                chart: {
                    type: 'area',
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
                colors: ['#3b82f6'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
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
                        },
                        formatter: function(value) {
                            return '$' + value.toFixed(0);
                        }
                    }
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
                            return '$' + value.toFixed(2);
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
