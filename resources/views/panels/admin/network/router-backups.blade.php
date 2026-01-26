@extends('panels.layouts.app')

@section('title', 'Router Backups')

@section('content')
<div class="space-y-6" x-data="routerBackups()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Router Backups</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage configuration backups for {{ $router->name }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('panel.admin.network.routers') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Routers
                    </a>
                    <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Backup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center gap-4">
                <label for="backup_type_filter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Type:</label>
                <select id="backup_type_filter" x-model="filterType" @change="applyFilter()" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">All Types</option>
                    <option value="manual">Manual</option>
                    <option value="automatic">Automatic</option>
                    <option value="pre_import">Pre-Import</option>
                    <option value="pre_configuration">Pre-Configuration</option>
                    <option value="scheduled">Scheduled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($backups as $backup)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $backup->notes }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($backup->backup_type === 'manual') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                    @elseif($backup->backup_type === 'automatic') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                    @elseif($backup->backup_type === 'pre_import') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                    @elseif($backup->backup_type === 'pre_configuration') bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $backup->backup_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $backup->notes ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $backup->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($backup->created_by)
                                    {{ $backup->creator->name ?? 'User #'.$backup->created_by }}
                                @else
                                    System
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button @click="restoreBackup({{ $backup->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-3">
                                    Restore
                                </button>
                                @if($backup->file_path)
                                <a href="{{ route('panel.admin.network.routers.backup.download', [$router->id, $backup->id]) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                    Download
                                </a>
                                @endif
                                <button @click="deleteBackup({{ $backup->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-4 text-gray-500 dark:text-gray-400">No backups available</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Create your first backup to get started</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($backups->hasPages())
                <div class="mt-4">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Create New Backup
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="backup_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Backup Name</label>
                                    <input type="text" id="backup_name" x-model="newBackup.name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="e.g., Pre-upgrade backup">
                                </div>

                                <div>
                                    <label for="backup_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Backup Type</label>
                                    <select id="backup_type" x-model="newBackup.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="manual">Manual</option>
                                        <option value="automatic">Automatic</option>
                                        <option value="pre_configuration">Pre-Configuration</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="backup_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason (Optional)</label>
                                    <textarea id="backup_reason" x-model="newBackup.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Brief description of why this backup is being created..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="createBackup()" :disabled="isCreating" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-text="isCreating ? 'Creating...' : 'Create Backup'"></span>
                    </button>
                    <button @click="showCreateModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function routerBackups() {
    return {
        showCreateModal: false,
        isCreating: false,
        filterType: '',
        routerId: {{ $router->id }},
        backupNameMaxLength: 100,
        newBackup: {
            name: '',
            type: 'manual',
            reason: ''
        },
        
        async createBackup() {
            const trimmedName = this.newBackup.name ? this.newBackup.name.trim() : '';
            if (!trimmedName) {
                this.showNotification('Please enter a backup name', 'error');
                return;
            }
            if (trimmedName.length > this.backupNameMaxLength) {
                this.showNotification(`Backup name must be at most ${this.backupNameMaxLength} characters long`, 'error');
                return;
            }
            if (!/^[A-Za-z0-9_\- ]+$/.test(trimmedName)) {
                this.showNotification('Backup name may only contain letters, numbers, spaces, hyphens, and underscores.', 'error');
                return;
            }
            this.newBackup.name = trimmedName;
            
            this.isCreating = true;
            try {
                const response = await fetch('{{ route('panel.admin.routers.backup.create', ['routerId' => $router->id]) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newBackup)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Backup created successfully!', 'success');
                    this.showCreateModal = false;
                    this.newBackup = { name: '', type: 'manual', reason: '' };
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification(`Failed to create backup: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Backup creation error:', error);
                this.showNotification('Failed to create backup. Please try again.', 'error');
            } finally {
                this.isCreating = false;
            }
        },
        
        async restoreBackup(backupId) {
            if (!confirm('Are you sure you want to restore this backup? Current configuration will be replaced.')) {
                return;
            }
            
            try {
                const response = await fetch('{{ route('panel.admin.routers.backup.restore', ['routerId' => $router->id]) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ backup_id: backupId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Backup restored successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showNotification(`Failed to restore backup: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Backup restore error:', error);
                this.showNotification('Failed to restore backup. Please try again.', 'error');
            }
        },
        
        async deleteBackup(backupId) {
            if (!confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
                return;
            }
            
            try {
                const url = '{{ route('panel.admin.routers.backup.destroy', ['routerId' => $router->id, 'backupId' => '__BACKUP_ID__']) }}';
                const response = await fetch(url.replace('__BACKUP_ID__', backupId), {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Backup deleted successfully!', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification(`Failed to delete backup: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Backup deletion error:', error);
                this.showNotification('Failed to delete backup. Please try again.', 'error');
            }
        },
        
        applyFilter() {
            const url = new URL(window.location.href);
            if (this.filterType) {
                url.searchParams.set('type', this.filterType);
            } else {
                url.searchParams.delete('type');
            }
            window.location.href = url.toString();
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
@endsection
