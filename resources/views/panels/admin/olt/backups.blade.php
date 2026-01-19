@extends('panels.layouts.app')

@section('title', 'Backup Management')

@section('content')
<div class="space-y-6" x-data="backupManagement()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Backup Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage OLT configuration backups and schedules</p>
                </div>
                <button @click="showScheduleModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Schedule Backup
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Backups</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="backups.length">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Scheduled</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="schedules.length">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Today's Backups</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="todayBackupsCount">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Size</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="formatBytes(totalSize)">0 B</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Schedules -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Backup Schedules</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OLT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Backup</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Next Backup</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="schedule in schedules" :key="schedule.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="schedule.olt_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="schedule.frequency"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="schedule.time"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="schedule.last_backup ? new Date(schedule.last_backup).toLocaleString() : 'Never'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="schedule.next_backup ? new Date(schedule.next_backup).toLocaleString() : 'N/A'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="schedule.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                        x-text="schedule.is_active ? 'Active' : 'Inactive'">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button @click="editSchedule(schedule)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Edit</button>
                                    <button @click="runBackupNow(schedule.olt_id)" class="text-green-600 hover:text-green-900 dark:text-green-400">Run Now</button>
                                    <button @click="deleteSchedule(schedule.id)" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Backup History -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Backup History</h2>
                <select x-model="filterOltId" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All OLTs</option>
                    <template x-for="olt in uniqueOlts" :key="olt.id">
                        <option :value="olt.id" x-text="olt.name"></option>
                    </template>
                </select>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OLT</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">File Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="backup in filteredBackups" :key="backup.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="backup.olt_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400" x-text="backup.file_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatBytes(backup.file_size)"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="backup.backup_type === 'auto' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'"
                                        x-text="backup.backup_type">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="new Date(backup.created_at).toLocaleString()"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button @click="downloadBackup(backup.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Download</button>
                                    <button @click="compareBackup(backup.id)" class="text-green-600 hover:text-green-900 dark:text-green-400">Compare</button>
                                    <button @click="restoreBackup(backup.id)" class="text-orange-600 hover:text-orange-900 dark:text-orange-400">Restore</button>
                                    <button @click="deleteBackup(backup.id)" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div x-show="showScheduleModal" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showScheduleModal = false"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Schedule Backup</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select OLT</label>
                            <select x-model="scheduleForm.olt_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Choose OLT...</option>
                                <template x-for="olt in uniqueOlts" :key="olt.id">
                                    <option :value="olt.id" x-text="olt.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequency</label>
                            <select x-model="scheduleForm.frequency" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time</label>
                            <input type="time" x-model="scheduleForm.time" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" x-model="scheduleForm.is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="saveSchedule" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save Schedule
                    </button>
                    <button @click="showScheduleModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function backupManagement() {
    return {
        backups: [],
        schedules: [],
        filterOltId: '',
        showScheduleModal: false,
        scheduleForm: {
            olt_id: '',
            frequency: 'daily',
            time: '02:00',
            is_active: true
        },
        get uniqueOlts() {
            const olts = new Map();
            this.backups.forEach(backup => {
                if (!olts.has(backup.olt_id)) {
                    olts.set(backup.olt_id, { id: backup.olt_id, name: backup.olt_name });
                }
            });
            return Array.from(olts.values());
        },
        get filteredBackups() {
            if (!this.filterOltId) return this.backups;
            return this.backups.filter(b => b.olt_id === parseInt(this.filterOltId));
        },
        get todayBackupsCount() {
            const today = new Date().toDateString();
            return this.backups.filter(b => new Date(b.created_at).toDateString() === today).length;
        },
        get totalSize() {
            return this.backups.reduce((sum, b) => sum + b.file_size, 0);
        },
        init() {
            this.loadBackups();
            this.loadSchedules();
        },
        async loadBackups() {
            // Mock data - replace with actual API call
            this.backups = [
                {
                    id: 1,
                    olt_id: 1,
                    olt_name: 'Main OLT',
                    file_name: 'olt_1_backup_2024-01-17_120530.cfg',
                    file_size: 148650,
                    backup_type: 'auto',
                    created_at: new Date(Date.now() - 86400000).toISOString()
                },
                {
                    id: 2,
                    olt_id: 1,
                    olt_name: 'Main OLT',
                    file_name: 'olt_1_backup_2024-01-16_120530.cfg',
                    file_size: 147200,
                    backup_type: 'auto',
                    created_at: new Date(Date.now() - 172800000).toISOString()
                }
            ];
        },
        async loadSchedules() {
            // Mock data
            this.schedules = [
                {
                    id: 1,
                    olt_id: 1,
                    olt_name: 'Main OLT',
                    frequency: 'daily',
                    time: '02:00',
                    last_backup: new Date(Date.now() - 86400000).toISOString(),
                    next_backup: new Date(Date.now() + (86400000 - new Date().getHours() * 3600000)).toISOString(),
                    is_active: true
                }
            ];
        },
        formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
        },
        async saveSchedule() {
            if (!this.scheduleForm.olt_id) {
                alert('Please select an OLT');
                return;
            }
            // API call to save schedule
            alert('Schedule saved successfully!');
            this.showScheduleModal = false;
            this.loadSchedules();
        },
        async runBackupNow(oltId) {
            if (!confirm('Create backup now?')) return;
            try {
                const response = await fetch(`/api/v1/olt/${oltId}/backup`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                alert(data.message);
                this.loadBackups();
            } catch (error) {
                console.error('Backup failed:', error);
            }
        },
        async downloadBackup(backupId) {
            alert('Downloading backup ID: ' + backupId);
        },
        async compareBackup(backupId) {
            alert('Compare functionality - show diff between this backup and current config');
        },
        async restoreBackup(backupId) {
            if (!confirm('Restore this backup? This will overwrite current configuration.')) return;
            alert('Restore backup ID: ' + backupId);
        },
        async deleteBackup(backupId) {
            if (!confirm('Delete this backup?')) return;
            alert('Backup deleted');
            this.loadBackups();
        },
        editSchedule(schedule) {
            this.scheduleForm = { ...schedule };
            this.showScheduleModal = true;
        },
        async deleteSchedule(scheduleId) {
            if (!confirm('Delete this schedule?')) return;
            alert('Schedule deleted');
            this.loadSchedules();
        }
    }
}
</script>

<style nonce="{{ csp_nonce() }}">
[x-cloak] { display: none !important; }
</style>
@endsection
