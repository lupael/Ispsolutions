@props(['router'])

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="failoverStatus()">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Failover Status</h3>
            <button @click="refreshStatus()" :disabled="isRefreshing" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition disabled:opacity-50">
                <svg class="w-4 h-4 mr-1" :class="{'animate-spin': isRefreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>

        <div class="space-y-4">
            <!-- Failover Status Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Current Mode</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ strtoupper($router->primary_auth ?? 'router') }}
                            </p>
                        </div>
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">RADIUS Health</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <span class="inline-flex items-center">
                                    @if($router->radius_healthy ?? false)
                                        <span class="flex h-2 w-2 mr-2">
                                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                        </span>
                                        Healthy
                                    @else
                                        <span class="flex h-2 w-2 mr-2">
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                        </span>
                                        Unhealthy
                                    @endif
                                </span>
                            </p>
                        </div>
                        <svg class="w-8 h-8 {{ ($router->radius_healthy ?? false) ? 'text-green-500' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Netwatch Status</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ ($router->netwatch_enabled ?? false) ? 'Enabled' : 'Disabled' }}
                            </p>
                        </div>
                        <svg class="w-8 h-8 {{ ($router->netwatch_enabled ?? false) ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Failover Enabled</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ ($router->failover_enabled ?? false) ? 'Yes' : 'No' }}
                            </p>
                        </div>
                        <svg class="w-8 h-8 {{ ($router->failover_enabled ?? false) ? 'text-green-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Last Failover Event -->
            @if($router->last_failover_at ?? false)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                            Last failover event: {{ $router->last_failover_at->diffForHumans() }}
                            @if($router->last_failover_reason)
                                - {{ $router->last_failover_reason }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
                @if(!($router->failover_enabled ?? false))
                    <button @click="configureFailover()" :disabled="isLoading" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Configure Failover
                    </button>
                @endif
                
                <button @click="switchMode('radius')" :disabled="isLoading || currentMode === 'radius'" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Switch to RADIUS
                </button>

                <button @click="switchMode('router')" :disabled="isLoading || currentMode === 'router'" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    Switch to Router
                </button>

                <button @click="testRadius()" :disabled="isLoading" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Test RADIUS
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function failoverStatus() {
    return {
        isLoading: false,
        isRefreshing: false,
        routerId: {{ $router->id }},
        currentMode: '{{ $router->primary_auth ?? "router" }}',
        
        async refreshStatus() {
            this.isRefreshing = true;
            try {
                setTimeout(() => window.location.reload(), 500);
            } catch (error) {
                console.error('Refresh error:', error);
            } finally {
                this.isRefreshing = false;
            }
        },
        
        async configureFailover() {
            if (!confirm('Configure failover for this router? This will enable automatic switching between RADIUS and local authentication.')) return;
            
            this.isLoading = true;
            try {
                const response = await fetch('{{ route("panel.admin.routers.failover.configure", $router->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Failover configured successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(`Failed to configure failover: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Configure error:', error);
                this.showNotification('Failed to configure failover. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async switchMode(mode) {
            if (!confirm(`Switch authentication mode to ${mode.toUpperCase()}?`)) return;
            
            this.isLoading = true;
            try {
                const radiusUrl = '{{ route("panel.admin.routers.failover.switch-to-radius", $router->id) }}';
                const routerUrl = '{{ route("panel.admin.routers.failover.switch-to-router", $router->id) }}';
                const endpoint = mode === 'radius' ? radiusUrl : routerUrl;
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification(`Switched to ${mode.toUpperCase()} mode successfully!`, 'success');
                    this.currentMode = mode;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(`Failed to switch mode: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Switch mode error:', error);
                this.showNotification('Failed to switch mode. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async testRadius() {
            this.isLoading = true;
            try {
                const response = await fetch('{{ route("panel.admin.routers.failover.test-connection", ["routerId" => $router->id]) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification(`RADIUS test successful! Response time: ${data.response_time || 'N/A'}ms`, 'success');
                } else {
                    this.showNotification(`RADIUS test failed: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Test error:', error);
                this.showNotification('Failed to test RADIUS. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };
            
            notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endpush
