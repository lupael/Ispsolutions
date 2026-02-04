@extends('panels.layouts.app')

@section('title', 'Scheduled Configuration Templates')

@section('content')
<div class="space-y-6" x-data="scheduledTemplates()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Scheduled Configuration Templates</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Automate router configuration changes with scheduled templates</p>
                </div>
                <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Schedule
                </button>
            </div>
        </div>
    </div>

    <!-- Active Schedules -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Active Schedules</h2>
            
            <div class="space-y-4">
                <template x-for="schedule in activeSchedules" :key="schedule.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-indigo-300 dark:hover:border-indigo-700 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="schedule.name"></h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': schedule.status === 'active',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': schedule.status === 'pending',
                                              'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': schedule.status === 'paused'
                                          }"
                                          x-text="schedule.status.toUpperCase()">
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="schedule.description"></p>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Schedule Type:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100" x-text="schedule.frequency"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Next Run:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100" x-text="formatDateTime(schedule.next_run)"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Target Routers:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100" x-text="schedule.router_count"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Last Run:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100" x-text="schedule.last_run ? formatDateTime(schedule.last_run) : 'Never'"></span>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center space-x-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Configuration:</span>
                                    <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded" x-text="schedule.config_type"></span>
                                </div>
                            </div>

                            <div class="flex flex-col space-y-2 ml-4">
                                <button @click="editSchedule(schedule.id)" class="px-3 py-1 text-xs font-semibold text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    Edit
                                </button>
                                <button @click="toggleSchedule(schedule.id)" class="px-3 py-1 text-xs font-semibold text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                    <span x-text="schedule.status === 'active' ? 'Pause' : 'Resume'"></span>
                                </button>
                                <button @click="viewHistory(schedule.id)" class="px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    History
                                </button>
                                <button @click="deleteSchedule(schedule.id)" class="px-3 py-1 text-xs font-semibold text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="activeSchedules.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-4 text-gray-500 dark:text-gray-400">No active schedules. Create your first scheduled configuration.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Completed Schedules -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Completed Schedules</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Executed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Result</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="execution in completedExecutions" :key="execution.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="execution.schedule_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="execution.config_type"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDateTime(execution.executed_at)"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': execution.status === 'success',
                                              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': execution.status === 'failed',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': execution.status === 'partial'
                                          }"
                                          x-text="execution.status.toUpperCase()">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span x-text="`${execution.success_count}/${execution.total_count} routers`"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="viewExecutionDetails(execution.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Details
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="completedExecutions.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No completed executions
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showCreateModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create Scheduled Configuration</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Schedule Name</label>
                            <input type="text" x-model="newSchedule.name" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea x-model="newSchedule.description" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Configuration Type</label>
                            <select x-model="newSchedule.config_type" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select type...</option>
                                <option value="radius">RADIUS Configuration</option>
                                <option value="ppp">PPP Configuration</option>
                                <option value="firewall">Firewall Rules</option>
                                <option value="backup">Create Backup</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Frequency</label>
                            <select x-model="newSchedule.frequency" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="once">Once</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Schedule Date & Time</label>
                            <input type="datetime-local" x-model="newSchedule.scheduled_at" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Target Routers</label>
                            <select x-model="newSchedule.router_ids" multiple size="5" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <template x-for="router in routers" :key="router.id">
                                    <option :value="router.id" x-text="`${router.name} (${router.ip_address})`"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple routers</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="createSchedule()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Create Schedule
                    </button>
                    <button @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-700 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/notification-helper.js') }}" nonce="{{ csp_nonce() }}"></script>
<script nonce="{{ csp_nonce() }}">
function scheduledTemplates() {
    return {
        activeSchedules: [],
        completedExecutions: [],
        routers: [],
        showCreateModal: false,
        newSchedule: {
            name: '',
            description: '',
            config_type: '',
            frequency: 'once',
            scheduled_at: '',
            router_ids: []
        },
        
        async init() {
            await this.loadRouters();
            await this.loadSchedules();
            await this.loadExecutions();
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
        
        async loadSchedules() {
            try {
                const response = await fetch('/api/routers/scheduled-templates', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.activeSchedules = data.schedules || [];
                }
            } catch (error) {
                console.error('Error loading schedules:', error);
            }
        },
        
        async loadExecutions() {
            try {
                const response = await fetch('/api/routers/scheduled-templates/executions', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.completedExecutions = data.executions || [];
                }
            } catch (error) {
                console.error('Error loading executions:', error);
            }
        },
        
        async createSchedule() {
            // Client-side validation
            if (!this.newSchedule.name || !this.newSchedule.name.trim()) {
                window.showNotification('Schedule name is required', 'error');
                return;
            }
            
            if (!this.newSchedule.config_type) {
                window.showNotification('Configuration type is required', 'error');
                return;
            }
            
            if (!this.newSchedule.frequency) {
                window.showNotification('Frequency is required', 'error');
                return;
            }
            
            if (!this.newSchedule.scheduled_at) {
                window.showNotification('Schedule date and time is required', 'error');
                return;
            }
            
            if (!this.newSchedule.router_ids || this.newSchedule.router_ids.length === 0) {
                window.showNotification('Please select at least one router', 'error');
                return;
            }
            
            try {
                const response = await fetch('/api/routers/scheduled-templates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newSchedule)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.showNotification('Schedule created successfully', 'success');
                    this.showCreateModal = false;
                    this.resetNewSchedule();
                    await this.loadSchedules();
                } else {
                    window.showNotification(data.message || 'Failed to create schedule', 'error');
                }
            } catch (error) {
                console.error('Error creating schedule:', error);
                window.showNotification('Error creating schedule', 'error');
            }
        },
        
        async toggleSchedule(scheduleId) {
            try {
                const response = await fetch(`/api/routers/scheduled-templates/${scheduleId}/toggle`, {
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
                    await this.loadSchedules();
                }
            } catch (error) {
                console.error('Error toggling schedule:', error);
                window.showNotification('Error toggling schedule', 'error');
            }
        },
        
        async deleteSchedule(scheduleId) {
            if (!confirm('Are you sure you want to delete this schedule?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/routers/scheduled-templates/${scheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                window.showNotification(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    await this.loadSchedules();
                }
            } catch (error) {
                console.error('Error deleting schedule:', error);
                window.showNotification('Error deleting schedule', 'error');
            }
        },
        
        async editSchedule(scheduleId) {
            const schedule = this.activeSchedules.find(s => s.id === scheduleId);
            if (!schedule) {
                window.showNotification('Schedule not found', 'error');
                return;
            }

            // Populate the modal with existing schedule data for editing
            this.newSchedule = {
                name: schedule.name || '',
                description: schedule.description || '',
                config_type: schedule.config_type || '',
                frequency: schedule.frequency || 'once',
                scheduled_at: schedule.scheduled_at || '',
                router_ids: schedule.router_ids || []
            };
            
            this.showCreateModal = true;
            window.showNotification(`Editing schedule: ${schedule.name}`, 'info');
        },
        
        viewHistory(scheduleId) {
            const schedule = this.activeSchedules.find(s => s.id === scheduleId);
            if (!schedule) {
                window.showNotification('Schedule not found', 'error');
                return;
            }

            // Filter execution history for this schedule
            const historyEntries = this.completedExecutions.filter(e => e.schedule_id === scheduleId);
            
            if (!historyEntries.length) {
                window.showNotification(`No execution history found for "${schedule.name}"`, 'info');
                return;
            }
            
            // Build a readable history summary
            let message = `Execution History for "${schedule.name}":\n\n`;
            historyEntries.slice(0, 10).forEach((entry, index) => {
                const status = entry.status || 'Unknown';
                const finishedAt = this.formatDateTime(entry.executed_at || entry.completed_at);
                const result = entry.success_count && entry.total_count 
                    ? `${entry.success_count}/${entry.total_count} succeeded` 
                    : 'N/A';
                message += `${index + 1}. ${status.toUpperCase()} - ${finishedAt} (${result})\n`;
            });
            
            if (historyEntries.length > 10) {
                message += `\n... and ${historyEntries.length - 10} more executions`;
            }
            
            alert(message);
        },
        
        viewExecutionDetails(executionId) {
            const execution = this.completedExecutions.find(e => e.id === executionId);
            if (!execution) {
                window.showNotification('Execution details not found', 'error');
                return;
            }

            const status = execution.status || 'unknown';
            const executedAt = execution.executed_at || execution.completed_at || execution.started_at;
            const scheduleName = execution.schedule_name || 'Unknown';
            const successCount = execution.success_count || 0;
            const totalCount = execution.total_count || 0;

            const message = 
                `Execution Details:\n\n` +
                `Schedule: ${scheduleName}\n` +
                `Status: ${status.toUpperCase()}\n` +
                `Executed: ${this.formatDateTime(executedAt)}\n` +
                `Results: ${successCount}/${totalCount} routers succeeded\n` +
                `Configuration Type: ${execution.config_type || 'N/A'}`;

            alert(message);
        },
        
        resetNewSchedule() {
            this.newSchedule = {
                name: '',
                description: '',
                config_type: '',
                frequency: 'once',
                scheduled_at: '',
                router_ids: []
            };
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
