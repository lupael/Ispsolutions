@props(['serviceTypeDistribution'])

@php
    $labels = ['PPPoE', 'Hotspot', 'Static IP'];
    $values = [
        $serviceTypeDistribution['pppoe'] ?? 0,
        $serviceTypeDistribution['hotspot'] ?? 0,
        $serviceTypeDistribution['static'] ?? 0
    ];
    $total = array_sum($values);
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Service Type Distribution</h3>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500 dark:text-gray-400">Total: {{ $total }}</span>
        </div>
    </div>
    
    <div id="service-type-chart" style="min-height: 300px;"></div>
    
    <div class="mt-4 grid grid-cols-3 gap-4 text-center">
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <div class="text-sm font-medium text-blue-700 dark:text-blue-300">PPPoE</div>
            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $serviceTypeDistribution['pppoe'] ?? 0 }}</div>
        </div>
        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <div class="text-sm font-medium text-green-700 dark:text-green-300">Hotspot</div>
            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $serviceTypeDistribution['hotspot'] ?? 0 }}</div>
        </div>
        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <div class="text-sm font-medium text-purple-700 dark:text-purple-300">Static IP</div>
            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $serviceTypeDistribution['static'] ?? 0 }}</div>
        </div>
    </div>
    
    <script nonce="{{ $cspNonce ?? '' }}">
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not loaded');
                return;
            }
            
            const chartElement = document.querySelector('#service-type-chart');
            if (!chartElement) return;
            
            const options = {
                series: @json($values),
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'inherit',
                    background: 'transparent'
                },
                labels: @json($labels),
                colors: ['#3b82f6', '#10b981', '#8b5cf6'],
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#9ca3af'
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px',
                                    color: '#9ca3af'
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 'bold',
                                    color: '#111827',
                                    formatter: function(val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '14px',
                                    color: '#9ca3af',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val.toFixed(1) + '%';
                    },
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold'
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
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            labels: {
                                                value: {
                                                    color: isDark ? '#f3f4f6' : '#111827'
                                                }
                                            }
                                        }
                                    }
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
