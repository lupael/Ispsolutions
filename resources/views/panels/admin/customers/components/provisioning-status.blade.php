@props(['customer'])

{{-- Note: This component expects customer provisioning properties that may need to be added via a database migration:
     - provisioned_on_router (boolean)
     - router relationship
     - pppoe_profile (string)
     - ip_address (string) 
     - last_provisioned_at (timestamp)
     
     If these don't exist, this component will show N/A for missing data.
--}}

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="provisioningStatus()">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Provisioning Status</h3>
            <button @click="refreshStatus()" :disabled="isRefreshing" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition disabled:opacity-50">
                <svg class="w-4 h-4 mr-1" :class="{'animate-spin': isRefreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>

        <div class="space-y-4">
            <!-- Provisioning Status -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <span class="inline-flex items-center">
                                    @if($customer->provisioned_on_router ?? false)
                                        <span class="flex h-2 w-2 mr-2">
                                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                        </span>
                                        Provisioned
                                    @else
                                        <span class="flex h-2 w-2 mr-2">
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-400"></span>
                                        </span>
                                        Not Provisioned
                                    @endif
                                </span>
                            </p>
                        </div>
                        <svg class="w-8 h-8 {{ ($customer->provisioned_on_router ?? false) ? 'text-green-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Router</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ optional($customer->router)->name ?? 'N/A' }}
                    </p>
                    @if(optional($customer->router)->ip_address)
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ optional($customer->router)->ip_address }}</p>
                    @endif
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Profile</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $customer->pppoe_profile ?? 'N/A' }}
                    </p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">IP Address</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $customer->ip_address ?? 'N/A' }}
                    </p>
                </div>
            </div>

            @if($customer->last_provisioned_at ?? false)
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-200">
                            Last provisioned {{ $customer->last_provisioned_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
                @if(!($customer->provisioned_on_router ?? false))
                    <button @click="provisionNow()" :disabled="isLoading" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Provision Now
                    </button>
                @else
                    <button @click="updateOnRouter()" :disabled="isLoading" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Update on Router
                    </button>
                    
                    <button @click="removeFromRouter()" :disabled="isLoading" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remove from Router
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function provisioningStatus() {
    return {
        isLoading: false,
        isRefreshing: false,
        customerId: {{ $customer->id }},
        
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
        
        async provisionNow() {
            if (!confirm('Provision this customer on the router?')) return;
            
            this.isLoading = true;
            try {
                const response = await fetch(`/api/customers/${this.customerId}/provision`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Customer provisioned successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(`Failed to provision: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Provision error:', error);
                this.showNotification('Failed to provision customer. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async updateOnRouter() {
            if (!confirm('Update this customer\'s configuration on the router?')) return;
            
            this.isLoading = true;
            try {
                const response = await fetch(`/api/customers/${this.customerId}/update-router`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Router configuration updated successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(`Failed to update: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Update error:', error);
                this.showNotification('Failed to update configuration. Please try again.', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async removeFromRouter() {
            if (!confirm('Remove this customer from the router? This will disconnect their service.')) return;
            
            this.isLoading = true;
            try {
                const response = await fetch(`/api/customers/${this.customerId}/deprovision`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Customer removed from router successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(`Failed to remove: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Remove error:', error);
                this.showNotification('Failed to remove customer. Please try again.', 'error');
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
