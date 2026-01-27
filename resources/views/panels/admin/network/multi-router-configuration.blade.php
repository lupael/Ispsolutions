@extends('panels.layouts.app')

@section('title', 'Multi-Router Configuration')

@section('content')
<div class="space-y-6" x-data="multiRouterConfig()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Multi-Router Configuration</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Apply configuration changes to multiple routers simultaneously</p>
                </div>
                <button @click="resetSelection()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                    Reset Selection
                </button>
            </div>
        </div>
    </div>

    <!-- Router Selection -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Select Routers</h2>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="selectedRouters.length"></span> of <span x-text="routers.length"></span> selected
                    </span>
                    <button @click="selectAll()" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                        Select All
                    </button>
                    <button @click="deselectAll()" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 font-medium">
                        Deselect All
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="router in routers" :key="router.id">
                    <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700 transition"
                           :class="{'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': isSelected(router.id), 'border-gray-200 dark:border-gray-700': !isSelected(router.id)}">
                        <input type="checkbox" 
                               :value="router.id"
                               @change="toggleRouter(router.id)"
                               :checked="isSelected(router.id)"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1">
                        <div class="ml-3 flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="router.name"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <div x-text="router.ip_address"></div>
                                <div class="flex items-center mt-1">
                                    <span class="inline-block w-2 h-2 rounded-full mr-1"
                                          :class="router.status === 'online' ? 'bg-green-500' : 'bg-red-500'"></span>
                                    <span x-text="router.status || 'offline'"></span>
                                </div>
                            </div>
                        </div>
                    </label>
                </template>
            </div>

            <template x-if="routers.length === 0">
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No routers available</p>
                </div>
            </template>
        </div>
    </div>

    <!-- Configuration Type Selection -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-show="selectedRouters.length > 0">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Configuration Type</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700 transition"
                       :class="{'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': configurationType === 'radius', 'border-gray-200 dark:border-gray-700': configurationType !== 'radius'}">
                    <input type="radio" 
                           name="config_type" 
                           value="radius"
                           x-model="configurationType"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 mt-1">
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">RADIUS Configuration</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure RADIUS authentication settings</div>
                    </div>
                </label>

                <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700 transition"
                       :class="{'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': configurationType === 'ppp', 'border-gray-200 dark:border-gray-700': configurationType !== 'ppp'}">
                    <input type="radio" 
                           name="config_type" 
                           value="ppp"
                           x-model="configurationType"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 mt-1">
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">PPP Configuration</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure PPPoE profiles and settings</div>
                    </div>
                </label>

                <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700 transition"
                       :class="{'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': configurationType === 'firewall', 'border-gray-200 dark:border-gray-700': configurationType !== 'firewall'}">
                    <input type="radio" 
                           name="config_type" 
                           value="firewall"
                           x-model="configurationType"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 mt-1">
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Firewall Rules</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Configure firewall rules and policies</div>
                    </div>
                </label>

                <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:border-indigo-300 dark:hover:border-indigo-700 transition"
                       :class="{'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20': configurationType === 'backup', 'border-gray-200 dark:border-gray-700': configurationType !== 'backup'}">
                    <input type="radio" 
                           name="config_type" 
                           value="backup"
                           x-model="configurationType"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 mt-1">
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Create Backup</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Create configuration backups</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Configuration Parameters -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-show="selectedRouters.length > 0 && configurationType">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Configuration Parameters</h2>
            
            <!-- RADIUS Configuration -->
            <div x-show="configurationType === 'radius'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">RADIUS Server IP</label>
                    <input type="text" x-model="config.radius.server_ip" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="192.168.1.1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">RADIUS Port</label>
                    <input type="number" x-model="config.radius.port" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="1812">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">RADIUS Secret</label>
                    <input type="password" x-model="config.radius.secret" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Secret key">
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" x-model="config.radius.enable_accounting" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable RADIUS Accounting</span>
                    </label>
                </div>
            </div>

            <!-- PPP Configuration -->
            <div x-show="configurationType === 'ppp'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Name</label>
                    <input type="text" x-model="config.ppp.profile_name" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="default">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Local Address</label>
                    <input type="text" x-model="config.ppp.local_address" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="10.0.0.1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Remote Address Pool</label>
                    <input type="text" x-model="config.ppp.remote_pool" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="pool1">
                </div>
            </div>

            <!-- Firewall Configuration -->
            <div x-show="configurationType === 'firewall'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Firewall Template</label>
                    <select x-model="config.firewall.template" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select template...</option>
                        <option value="basic">Basic Protection</option>
                        <option value="advanced">Advanced Security</option>
                        <option value="custom">Custom Rules</option>
                    </select>
                </div>
                <div x-show="config.firewall.template === 'custom'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Rules</label>
                    <textarea x-model="config.firewall.custom_rules" rows="6" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter firewall rules..."></textarea>
                </div>
            </div>

            <!-- Backup Configuration -->
            <div x-show="configurationType === 'backup'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Backup Name</label>
                    <input type="text" x-model="config.backup.name" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Auto-generated if left empty">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
                    <textarea x-model="config.backup.notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional notes about this backup..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-show="selectedRouters.length > 0 && configurationType">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Ready to apply <strong x-text="configurationType"></strong> configuration to <strong x-text="selectedRouters.length"></strong> router(s)
                </div>
                <div class="flex space-x-3">
                    <button @click="previewConfiguration()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        Preview
                    </button>
                    <button @click="applyConfiguration()" :disabled="isApplying" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" :class="{'animate-spin': isApplying}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isApplying ? 'Applying...' : 'Apply Configuration'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-show="results.length > 0">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Application Results</h2>
            
            <div class="space-y-3">
                <template x-for="result in results" :key="result.router_id">
                    <div class="flex items-center justify-between p-3 rounded-lg border"
                         :class="{
                             'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20': result.success,
                             'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20': !result.success
                         }">
                        <div class="flex items-center space-x-3">
                            <svg class="w-5 h-5" 
                                 :class="result.success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <template x-if="result.success">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </template>
                                <template x-if="!result.success">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </template>
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="result.router_name"></div>
                                <div class="text-xs text-gray-600 dark:text-gray-400" x-text="result.message"></div>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded"
                              :class="result.success ? 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-200'"
                              x-text="result.success ? 'SUCCESS' : 'FAILED'">
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/notification-helper.js') }}" nonce="{{ csp_nonce() }}"></script>
<script nonce="{{ csp_nonce() }}">
function multiRouterConfig() {
    return {
        routers: [],
        selectedRouters: [],
        configurationType: '',
        config: {
            radius: {
                server_ip: '',
                port: 1812,
                secret: '',
                enable_accounting: true
            },
            ppp: {
                profile_name: 'default',
                local_address: '',
                remote_pool: ''
            },
            firewall: {
                template: '',
                custom_rules: ''
            },
            backup: {
                name: '',
                notes: ''
            }
        },
        isApplying: false,
        results: [],
        
        async init() {
            await this.loadRouters();
        },
        
        async loadRouters() {
            try {
                const response = await fetch('/api/routers', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.routers = data.data || data.routers || [];
                }
            } catch (error) {
                console.error('Error loading routers:', error);
            }
        },
        
        isSelected(routerId) {
            return this.selectedRouters.includes(routerId);
        },
        
        toggleRouter(routerId) {
            const index = this.selectedRouters.indexOf(routerId);
            if (index > -1) {
                this.selectedRouters.splice(index, 1);
            } else {
                this.selectedRouters.push(routerId);
            }
        },
        
        selectAll() {
            this.selectedRouters = this.routers.map(r => r.id);
        },
        
        deselectAll() {
            this.selectedRouters = [];
        },
        
        resetSelection() {
            this.selectedRouters = [];
            this.configurationType = '';
            this.results = [];
        },
        
        previewConfiguration() {
            if (!this.configurationType) {
                window.showNotification('Please select a configuration type to preview.', 'warning');
                return;
            }

            if (!this.selectedRouters || this.selectedRouters.length === 0) {
                window.showNotification('Please select at least one router to preview the configuration.', 'warning');
                return;
            }

            const config = this.config[this.configurationType];
            const routerNames = this.routers
                .filter(r => this.selectedRouters.includes(r.id))
                .map(r => r.name)
                .join(', ');

            const previewMessage =
                `Configuration Preview\n\n` +
                `Type: ${this.configurationType.toUpperCase()}\n` +
                `Routers (${this.selectedRouters.length}): ${routerNames}\n\n` +
                `Configuration:\n${JSON.stringify(config, null, 2)}`;

            alert(previewMessage);
            window.showNotification('Configuration preview displayed.', 'info');
        },
        
        async applyConfiguration() {
            // Validate configuration fields before submission
            const config = this.config[this.configurationType];
            
            if (this.configurationType === 'radius') {
                if (!config.server_ip || !config.server_ip.trim()) {
                    window.showNotification('RADIUS Server IP is required', 'error');
                    return;
                }
                if (!config.port || config.port < 1 || config.port > 65535) {
                    window.showNotification('Valid RADIUS Port (1-65535) is required', 'error');
                    return;
                }
                if (!config.secret || !config.secret.trim()) {
                    window.showNotification('RADIUS Secret is required', 'error');
                    return;
                }
            } else if (this.configurationType === 'ppp') {
                if (!config.profile_name || !config.profile_name.trim()) {
                    window.showNotification('PPP Profile Name is required', 'error');
                    return;
                }
                if (!config.local_address || !config.local_address.trim()) {
                    window.showNotification('Local Address is required', 'error');
                    return;
                }
                if (!config.remote_pool || !config.remote_pool.trim()) {
                    window.showNotification('Remote Address Pool is required', 'error');
                    return;
                }
            } else if (this.configurationType === 'firewall') {
                if (!config.template) {
                    window.showNotification('Firewall Template is required', 'error');
                    return;
                }
                if (config.template === 'custom' && (!config.custom_rules || !config.custom_rules.trim())) {
                    window.showNotification('Custom firewall rules are required when using custom template', 'error');
                    return;
                }
            }
            
            if (!confirm(`Are you sure you want to apply ${this.configurationType} configuration to ${this.selectedRouters.length} router(s)?`)) {
                return;
            }
            
            this.isApplying = true;
            this.results = [];
            
            try {
                const response = await fetch('/api/routers/bulk-configure', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        router_ids: this.selectedRouters,
                        configuration_type: this.configurationType,
                        config: this.config[this.configurationType]
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.results = data.results || [];
                    const successCount = this.results.filter(r => r.success).length;
                    window.showNotification(
                        `Configuration applied to ${successCount} of ${this.results.length} routers`,
                        successCount === this.results.length ? 'success' : 'warning'
                    );
                } else {
                    window.showNotification('Failed to apply configuration', 'error');
                }
            } catch (error) {
                console.error('Error applying configuration:', error);
                window.showNotification('Error applying configuration', 'error');
            } finally {
                this.isApplying = false;
            }
        },
        
    }
}
</script>
@endpush
@endsection
