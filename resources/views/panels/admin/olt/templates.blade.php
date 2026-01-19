@extends('panels.layouts.app')

@section('title', 'Configuration Templates')

@section('content')
<div class="space-y-6" x-data="configTemplates()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Configuration Templates</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage OLT configuration templates</p>
                </div>
                <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Template
                </button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Templates</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="templates.length">0</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Templates</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="templates.filter(t => t.is_active).length">0</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Vendors</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="uniqueVendors">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Templates</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="template in templates" :key="template.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="template.name"></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="template.vendor || 'Generic'"></p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full"
                                :class="template.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                x-text="template.is_active ? 'Active' : 'Inactive'">
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="template.description || 'No description'"></p>
                        <div class="flex space-x-2">
                            <button @click="viewTemplate(template)" class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                View
                            </button>
                            <button @click="editTemplate(template)" class="flex-1 px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                Edit
                            </button>
                            <button @click="deleteTemplate(template.id)" class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- View Template Modal -->
    <div x-show="showViewModal" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showViewModal = false"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4" x-text="selectedTemplate?.name"></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendor</label>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="selectedTemplate?.vendor || 'N/A'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="selectedTemplate?.model || 'N/A'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="selectedTemplate?.description || 'N/A'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Template Content</label>
                            <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded text-sm overflow-x-auto"><code x-text="selectedTemplate?.template_content"></code></pre>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="showViewModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Template Modal -->
    <div x-show="showCreateModal" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCreateModal = false"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                        <span x-text="editingTemplate ? 'Edit Template' : 'Create Template'"></span>
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <input type="text" x-model="formData.name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendor</label>
                                <select x-model="formData.vendor" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Generic</option>
                                    <option value="huawei">Huawei</option>
                                    <option value="zte">ZTE</option>
                                    <option value="fiberhome">Fiberhome</option>
                                    <option value="nokia">Nokia</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
                                <input type="text" x-model="formData.model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea x-model="formData.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Template Content</label>
                            <textarea x-model="formData.template_content" rows="12" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" placeholder="Enter configuration commands..."></textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use {{variable_name}} for template variables</p>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" x-model="formData.is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="saveTemplate" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce() }}">
function configTemplates() {
    return {
        templates: [],
        selectedTemplate: null,
        showViewModal: false,
        showCreateModal: false,
        editingTemplate: null,
        formData: {
            name: '',
            vendor: '',
            model: '',
            description: '',
            template_content: '',
            is_active: true
        },
        get uniqueVendors() {
            return new Set(this.templates.map(t => t.vendor).filter(v => v)).size;
        },
        init() {
            this.loadTemplates();
        },
        async loadTemplates() {
            // Mock data - replace with actual API call
            this.templates = [
                {
                    id: 1,
                    name: 'Basic ONU Configuration',
                    vendor: 'huawei',
                    model: 'MA5800',
                    description: 'Standard ONU provisioning template',
                    template_content: 'interface gpon 0/{{slot}}\nonu {{onu_id}} type {{onu_type}} sn {{serial_number}}',
                    is_active: true
                },
                {
                    id: 2,
                    name: 'QoS Profile',
                    vendor: 'zte',
                    model: 'C320',
                    description: 'Quality of Service configuration',
                    template_content: 'traffic-profile {{profile_name}}\nbandwidth {{bandwidth}}',
                    is_active: true
                }
            ];
        },
        viewTemplate(template) {
            this.selectedTemplate = template;
            this.showViewModal = true;
        },
        editTemplate(template) {
            this.editingTemplate = template;
            this.formData = { ...template };
            this.showCreateModal = true;
        },
        saveTemplate() {
            // Save logic here
            alert('Template saved successfully!');
            this.showCreateModal = false;
            this.resetForm();
            this.loadTemplates();
        },
        async deleteTemplate(id) {
            if (!confirm('Delete this template?')) return;
            // Delete logic here
            alert('Template deleted!');
            this.loadTemplates();
        },
        resetForm() {
            this.formData = {
                name: '',
                vendor: '',
                model: '',
                description: '',
                template_content: '',
                is_active: true
            };
            this.editingTemplate = null;
        }
    }
}
</script>

<style nonce="{{ csp_nonce() }}">
[x-cloak] { display: none !important; }
</style>
@endsection
