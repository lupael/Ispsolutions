@props(['operatorPerformance'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6']) }}>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Top Performing Operators</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">This Month</span>
    </div>
    
    @if($operatorPerformance && $operatorPerformance->isNotEmpty())
        <div class="space-y-4">
            @foreach($operatorPerformance->take(5) as $index => $operator)
                <div class="relative">
                    <!-- Operator Info -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-3">
                            <!-- Rank Badge -->
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                                @if($index === 0) bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300
                                @elseif($index === 1) bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300
                                @elseif($index === 2) bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300
                                @else bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400
                                @endif">
                                {{ $index + 1 }}
                            </div>
                            
                            <!-- Operator Details -->
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $operator['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $operator['operator_level'] === 30 ? 'Operator' : 'Sub-Operator' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Revenue Badge -->
                        <div class="text-right">
                            <div class="text-lg font-bold text-green-600 dark:text-green-400">
                                ${{ number_format($operator['monthly_revenue'], 2) }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Revenue</div>
                        </div>
                    </div>
                    
                    <!-- Performance Metrics -->
                    <div class="grid grid-cols-4 gap-2 mt-3 ml-11">
                        <div class="text-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Customers</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $operator['total_customers'] }}</div>
                        </div>
                        <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded">
                            <div class="text-xs text-green-600 dark:text-green-400 font-medium">Active</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $operator['active_customers'] }}</div>
                        </div>
                        <div class="text-center p-2 bg-purple-50 dark:bg-purple-900/20 rounded">
                            <div class="text-xs text-purple-600 dark:text-purple-400 font-medium">New</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $operator['new_customers_this_month'] }}</div>
                        </div>
                        <div class="text-center p-2 bg-orange-50 dark:bg-orange-900/20 rounded">
                            <div class="text-xs text-orange-600 dark:text-orange-400 font-medium">Tickets</div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $operator['tickets_resolved'] }}</div>
                        </div>
                    </div>
                    
                    <!-- Performance Indicator Bar -->
                    @php
                        $performanceScore = 0;
                        if ($operator['total_customers'] > 0) {
                            $activeRate = ($operator['active_customers'] / $operator['total_customers']) * 100;
                            $performanceScore = min(100, $activeRate);
                        }
                    @endphp
                    <div class="mt-3 ml-11">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Performance Score</span>
                            <span class="font-semibold">{{ number_format($performanceScore, 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-500
                                @if($performanceScore >= 90) bg-green-500
                                @elseif($performanceScore >= 75) bg-blue-500
                                @elseif($performanceScore >= 50) bg-yellow-500
                                @else bg-red-500
                                @endif" 
                                style="width: {{ $performanceScore }}%"></div>
                        </div>
                    </div>
                </div>
                
                @if(!$loop->last)
                    <div class="border-t border-gray-200 dark:border-gray-700 my-4"></div>
                @endif
            @endforeach
        </div>
        
        <!-- View All Link -->
        @if($operatorPerformance->count() > 5)
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                <a href="{{ route('panel.isp.operators') }}" 
                   class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                    View All Operators ({{ $operatorPerformance->count() }}) →
                </a>
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No operator data available</p>
        </div>
    @endif
</div>
