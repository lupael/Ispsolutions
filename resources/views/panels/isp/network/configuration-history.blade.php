@extends('panels.layouts.app')

@section('title', 'Configuration Change History')

@section('content')
<div class="space-y-6" x-data="configurationHistory()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configuration Change History</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Track and compare router configuration changes over time</p>
                </div>
                <div class="flex gap-3">
                    <select x-model="selectedRouter" @change="loadHistory()" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Routers</option>
                        <template x-for="router in routers" :key="router.id">
                            <option :value="router.id" x-text="router.name"></option>
                        </template>
                    </select>
                    <button @click="loadHistory()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" :class="{'animate-spin': isLoading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Options -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Change Type</label>
                    <select x-model="filterType" @change="loadHistory()" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Types</option>
                        <option value="radius">RADIUS Configuration</option>
                        <option value="ppp">PPP Configuration</option>
                        <option value="firewall">Firewall Rules</option>
                        <option value="backup">Backup</option>
                        <option value="restore">Restore</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                    <input type="date" x-model="dateFrom" @change="loadHistory()" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                    <input type="date" x-model="dateTo" @change="loadHistory()" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Modified By</label>
                    <input type="text" x-model="filterUser" @input="loadHistory()" placeholder="Username..." class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration History Timeline -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Configuration Changes</h2>
            
            <div class="space-y-4">
                <template x-for="(change, index) in changes" :key="change.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-indigo-300 dark:hover:border-indigo-700 transition">
                        <!-- Change Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="h-10 w-10 rounded-full flex items-center justify-center"
                                         :class="{
                                             'bg-green-100 dark:bg-green-900': change.action === 'create',
                                             'bg-blue-100 dark:bg-blue-900': change.action === 'update',
                                             'bg-red-100 dark:bg-red-900': change.action === 'delete',
                                             'bg-purple-100 dark:bg-purple-900': change.action === 'restore'
                                         }">
                                        <svg class="w-5 h-5" 
                                             :class="{
                                                 'text-green-600 dark:text-green-400': change.action === 'create',
                                                 'text-blue-600 dark:text-blue-400': change.action === 'update',
                                                 'text-red-600 dark:text-red-400': change.action === 'delete',
                                                 'text-purple-600 dark:text-purple-400': change.action === 'restore'
                                             }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <template x-if="change.action === 'create'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </template>
                                            <template x-if="change.action === 'update'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </template>
                                            <template x-if="change.action === 'delete'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </template>
                                            <template x-if="change.action === 'restore'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </template>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="change.description"></h3>
                                    <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="change.router_name"></span>
                                        <span>•</span>
                                        <span x-text="change.type"></span>
                                        <span>•</span>
                                        <span x-text="formatDateTime(change.created_at)"></span>
                                        <span>•</span>
                                        <span x-text="'By ' + change.user_name"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="viewDiff(change.id, index)" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                    <span x-show="!change.showDiff">View Diff</span>
                                    <span x-show="change.showDiff">Hide Diff</span>
                                </button>
                                <template x-if="change.action === 'update' || change.action === 'delete'">
                                    <button @click="revertChange(change.id)" class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                        Revert
                                    </button>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Diff View -->
                        <div x-show="change.showDiff" x-collapse>
                            <div class="mt-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Configuration Changes:</h4>
                                <div class="font-mono text-xs overflow-x-auto">
                                    <template x-if="change.diff && change.diff.length > 0">
                                        <div class="space-y-1">
                                            <template x-for="line in change.diff" :key="line.id">
                                                <div class="flex"
                                                     :class="{
                                                         'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200': line.type === 'removed',
                                                         'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200': line.type === 'added',
                                                         'text-gray-600 dark:text-gray-400': line.type === 'context'
                                                     }">
                                                    <span class="inline-block w-8 text-right pr-2 select-none" x-text="line.lineNum"></span>
                                                    <span class="inline-block w-4" x-text="line.type === 'removed' ? '-' : line.type === 'added' ? '+' : ' '"></span>
                                                    <span x-text="line.content"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!change.diff || change.diff.length === 0">
                                        <p class="text-gray-500 dark:text-gray-400">No detailed diff available</p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <template x-if="changes.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-4 text-gray-500 dark:text-gray-400" x-text="isLoading ? 'Loading...' : 'No configuration changes found'"></p>
                    </div>
                </template>
            </div>

            <!-- Pagination -->
            <div x-show="totalPages > 1" class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                    <span x-text="Math.min(currentPage * perPage, totalChanges)"></span> of 
                    <span x-text="totalChanges"></span> changes
                </div>
                <div class="flex space-x-2">
                    <button @click="previousPage()" :disabled="currentPage === 1" 
                            class="px-3 py-1 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button @click="nextPage()" :disabled="currentPage === totalPages" 
                            class="px-3 py-1 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/notification-helper.js') }}" nonce="{{ csp_nonce() }}"></script>
<script nonce="{{ csp_nonce() }}">
function configurationHistory() {
    return {
        isLoading: false,
        routers: [],
        changes: [],
        selectedRouter: '',
        filterType: '',
        filterUser: '',
        dateFrom: '',
        dateTo: '',
        currentPage: 1,
        perPage: 20,
        totalPages: 1,
        totalChanges: 0,
        
        async init() {
            await this.loadRouters();
            await this.loadHistory();
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
        
        async loadHistory() {
            this.isLoading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    router_id: this.selectedRouter,
                    type: this.filterType,
                    user: this.filterUser,
                    date_from: this.dateFrom,
                    date_to: this.dateTo
                });
                
                const response = await fetch(`/api/routers/configuration-history?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.changes = (data.changes || []).map(change => ({
                        ...change,
                        showDiff: false
                    }));
                    this.totalChanges = data.total || 0;
                    this.totalPages = Math.ceil(this.totalChanges / this.perPage);
                }
            } catch (error) {
                console.error('Error loading history:', error);
                window.showNotification('Failed to load configuration history', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        async viewDiff(changeId, index) {
            const change = this.changes[index];
            change.showDiff = !change.showDiff;
            
            if (change.showDiff && !change.diff) {
                try {
                    const response = await fetch(`/api/routers/configuration-history/${changeId}/diff`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        change.diff = data.diff || [];
                    }
                } catch (error) {
                    console.error('Error loading diff:', error);
                    change.showDiff = false;
                    window.showNotification('Failed to load diff', 'error');
                }
            }
        },
        
        async revertChange(changeId) {
            if (!confirm('Are you sure you want to revert this configuration change? This will restore the previous state.')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/routers/configuration-history/${changeId}/revert`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                window.showNotification(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    await this.loadHistory();
                }
            } catch (error) {
                console.error('Error reverting change:', error);
                window.showNotification('Failed to revert configuration', 'error');
            }
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadHistory();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadHistory();
            }
        },
        
        formatDateTime(timestamp) {
            if (!timestamp) return 'N/A';
            return new Date(timestamp).toLocaleString();
        },
        
    }
}
</script>
@endpush
@endsection
