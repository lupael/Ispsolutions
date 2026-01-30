@extends('panels.layouts.app')

@section('title', 'Zero-Touch Router Provisioning')

@section('content')
<div class="w-full px-4 py-4">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h5 class="mb-0 text-lg font-semibold">
                        <i class="fas fa-cogs mr-2"></i>Zero-Touch Router Provisioning
                    </h5>
                    <div>
                        <a href="{{ route('panel.admin.routers.provision.templates') }}" class="px-3 py-1 text-sm px-4 py-2 rounded border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white">
                            <i class="fas fa-file-code mr-1"></i>Manage Templates
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Router Selection -->
                    <div class="grid grid-cols-12 gap-4 mb-4">
                        <div class="md:col-span-6 col-span-12">
                            <label for="router-select" class="block text-sm font-medium text-gray-700 mb-1 font-bold">Select Router</label>
                            <select id="router-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Select a Router --</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" 
                                            data-ip="{{ $router->ip_address }}"
                                            {{ isset($selectedRouter) && $selectedRouter->id == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }} ({{ $router->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-6 col-span-12 flex items-end">
                            <button id="test-connection" class="px-4 py-2 rounded border border-cyan-600 text-cyan-600 hover:bg-cyan-600 hover:text-white" disabled>
                                <i class="fas fa-network-wired mr-1"></i>Test Connection
                            </button>
                        </div>
                    </div>

                    <!-- Router Info Panel -->
                    <div id="router-info" class="p-4 rounded-md mb-4 bg-blue-50 border border-blue-200 text-blue-800 hidden">
                        <div class="flex justify-between items-center">
                            <div>
                                <strong>Router:</strong> <span id="router-name"></span><br>
                                <strong>IP:</strong> <span id="router-ip"></span><br>
                                <strong>Status:</strong> <span id="router-status" class="px-2 py-1 text-xs rounded-full"></span>
                            </div>
                            <div>
                                <button id="create-backup" class="px-3 py-1 text-sm px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700">
                                    <i class="fas fa-save mr-1"></i>Create Backup
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Template Selection & Variables -->
                    <div id="provisioning-form" class="hidden">
                        <div class="grid grid-cols-12 gap-4 mb-4">
                            <div class="md:col-span-12 col-span-12">
                                <label for="template-select" class="block text-sm font-medium text-gray-700 mb-1 font-bold">Configuration Template</label>
                                <select id="template-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Select a Template --</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" data-type="{{ $template->template_type }}">
                                            {{ $template->name }} ({{ ucfirst(str_replace('_', ' ', $template->template_type)) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-gray-500">Select a pre-configured template or create a custom one</small>
                            </div>
                        </div>

                        <!-- Configuration Variables -->
                        <div id="variables-section" class="hidden">
                            <h6 class="mb-3 font-semibold">Configuration Variables</h6>
                            <div class="grid grid-cols-12 gap-4">
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-central-server-ip" class="block text-sm font-medium text-gray-700 mb-1">Central Server IP</label>
                                    <input type="text" id="var-central-server-ip" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="192.168.1.100" value="{{ request()->getHost() }}">
                                    <small class="text-gray-500">IP address of the central management server</small>
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-radius-server" class="block text-sm font-medium text-gray-700 mb-1">RADIUS Server</label>
                                    <input type="text" id="var-radius-server" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="127.0.0.1" value="127.0.0.1">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-radius-secret" class="block text-sm font-medium text-gray-700 mb-1">RADIUS Secret</label>
                                    <input type="text" id="var-radius-secret" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="mysecretkey" value="testing123">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-system-identity" class="block text-sm font-medium text-gray-700 mb-1">System Identity</label>
                                    <input type="text" id="var-system-identity" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="ISP-Router-01">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-hotspot-address" class="block text-sm font-medium text-gray-700 mb-1">Hotspot Address</label>
                                    <input type="text" id="var-hotspot-address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="10.5.50.1" value="10.5.50.1">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-dns-name" class="block text-sm font-medium text-gray-700 mb-1">Hotspot DNS Name</label>
                                    <input type="text" id="var-dns-name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="hotspot.local" value="hotspot.local">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-pppoe-pool-start" class="block text-sm font-medium text-gray-700 mb-1">PPPoE Pool Start</label>
                                    <input type="text" id="var-pppoe-pool-start" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="10.0.0.2" value="10.0.0.2">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-pppoe-pool-end" class="block text-sm font-medium text-gray-700 mb-1">PPPoE Pool End</label>
                                    <input type="text" id="var-pppoe-pool-end" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="10.0.0.254" value="10.0.0.254">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                    <input type="text" id="var-timezone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="Asia/Kolkata" value="UTC">
                                </div>
                                <div class="md:col-span-6 col-span-12 mb-3">
                                    <label for="var-ntp-server" class="block text-sm font-medium text-gray-700 mb-1">NTP Server</label>
                                    <input type="text" id="var-ntp-server" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                           placeholder="pool.ntp.org" value="pool.ntp.org">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 mt-4">
                            <button id="preview-config" class="px-4 py-2 rounded border border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white" disabled>
                                <i class="fas fa-eye mr-1"></i>Preview Configuration
                            </button>
                            <button id="execute-provision" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" disabled>
                                <i class="fas fa-play mr-1"></i>Execute Provisioning
                            </button>
                        </div>
                    </div>

                    <!-- Progress Section -->
                    <div id="progress-section" class="hidden mt-4">
                        <h6 class="font-semibold">Provisioning Progress</h6>
                        <div class="bg-gray-200 rounded-full h-8 mb-3">
                            <div id="progress-bar" class="bg-blue-600 h-8 rounded-full text-center text-white leading-8 transition-all duration-300" 
                                 role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <div id="progress-steps" class="space-y-2">
                            <!-- Steps will be added dynamically -->
                        </div>
                    </div>

                    <!-- Configuration Preview Modal -->
                    <!-- Note: Bootstrap modals require JavaScript framework - consider Alpine.js or similar -->
                    <div class="modal fade" id="preview-modal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Configuration Preview</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <pre id="config-preview" class="bg-gray-100 p-3 rounded" style="max-height: 500px; overflow-y: auto;"></pre>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="px-4 py-2 rounded bg-gray-600 text-white hover:bg-gray-700" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" id="apply-from-preview">Apply Configuration</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Logs & Backups -->
                    @if(isset($provisioningLogs) && $provisioningLogs->count() > 0)
                    <div class="mt-5">
                        <h6 class="font-semibold mb-3">Recent Provisioning Logs</h6>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left p-2">Date</th>
                                        <th class="text-left p-2">Action</th>
                                        <th class="text-left p-2">Template</th>
                                        <th class="text-left p-2">Status</th>
                                        <th class="text-left p-2">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($provisioningLogs as $log)
                                    <tr class="border-t">
                                        <td class="p-2">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="p-2">{{ ucfirst($log->action) }}</td>
                                        <td class="p-2">{{ $log->template->name ?? 'N/A' }}</td>
                                        <td class="p-2">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $log->status === 'success' ? 'bg-green-500 text-white' : ($log->status === 'failed' ? 'bg-red-500 text-white' : 'bg-yellow-500 text-white') }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td class="p-2">{{ $log->user->name ?? 'System' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if(isset($backups) && $backups->count() > 0)
                    <div class="mt-4">
                        <h6 class="font-semibold mb-3">Configuration Backups</h6>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left p-2">Date</th>
                                        <th class="text-left p-2">Type</th>
                                        <th class="text-left p-2">Created By</th>
                                        <th class="text-left p-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                    <tr class="border-t">
                                        <td class="p-2">{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="p-2">{{ ucfirst($backup->backup_type) }}</td>
                                        <td class="p-2">{{ $backup->creator->name ?? 'System' }}</td>
                                        <td class="p-2">
                                            <button class="px-3 py-1 text-sm px-4 py-2 rounded border border-yellow-600 text-yellow-600 hover:bg-yellow-600 hover:text-white rollback-btn" 
                                                    data-backup-id="{{ $backup->id }}">
                                                <i class="fas fa-undo mr-1"></i>Rollback
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ $cspNonce }}">
document.addEventListener('DOMContentLoaded', function() {
    const routerSelect = document.getElementById('router-select');
    const testConnectionBtn = document.getElementById('test-connection');
    const routerInfo = document.getElementById('router-info');
    const provisioningForm = document.getElementById('provisioning-form');
    const templateSelect = document.getElementById('template-select');
    const variablesSection = document.getElementById('variables-section');
    const previewBtn = document.getElementById('preview-config');
    const executeBtn = document.getElementById('execute-provision');
    const progressSection = document.getElementById('progress-section');
    const createBackupBtn = document.getElementById('create-backup');

    let selectedRouterId = null;
    let selectedTemplateId = null;

    // Router selection
    routerSelect.addEventListener('change', function() {
        selectedRouterId = this.value;
        if (selectedRouterId) {
            testConnectionBtn.disabled = false;
            const option = this.options[this.selectedIndex];
            document.getElementById('router-name').textContent = option.text;
            document.getElementById('router-ip').textContent = option.dataset.ip;
            routerInfo.classList.remove('hidden');
            provisioningForm.classList.remove('hidden');
        } else {
            testConnectionBtn.disabled = true;
            routerInfo.classList.add('hidden');
            provisioningForm.classList.add('hidden');
        }
    });

    // Test connection
    testConnectionBtn.addEventListener('click', async function() {
        if (!selectedRouterId) return;

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';

        try {
            const response = await fetch('{{ route("panel.admin.routers.provision.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ router_id: selectedRouterId })
            });

            const data = await response.json();
            const statusBadge = document.getElementById('router-status');
            
            if (data.success) {
                statusBadge.className = 'px-2 py-1 text-xs rounded-full bg-green-500 text-white';
                statusBadge.textContent = 'Connected';
                showAlert('success', data.message);
            } else {
                statusBadge.className = 'px-2 py-1 text-xs rounded-full bg-red-500 text-white';
                statusBadge.textContent = 'Disconnected';
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Connection test failed: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Template selection
    templateSelect.addEventListener('change', function() {
        selectedTemplateId = this.value;
        if (selectedTemplateId) {
            variablesSection.classList.remove('hidden');
            previewBtn.disabled = false;
            executeBtn.disabled = false;
            
            // Set default system identity based on router name
            const routerName = document.getElementById('router-name').textContent;
            document.getElementById('var-system-identity').value = routerName.split(' ')[0] || 'ISP-Router';
        } else {
            variablesSection.classList.add('hidden');
            previewBtn.disabled = true;
            executeBtn.disabled = true;
        }
    });

    // Preview configuration
    previewBtn.addEventListener('click', async function() {
        const variables = getVariables();
        
        try {
            const response = await fetch('{{ route("panel.admin.routers.provision.preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    template_id: selectedTemplateId,
                    variables: variables
                })
            });

            const data = await response.json();
            
            if (data.success) {
                document.getElementById('config-preview').textContent = JSON.stringify(data.configuration, null, 2);
                // TODO: Replace Bootstrap modal with Alpine.js or custom Tailwind modal
                const modal = new bootstrap.Modal(document.getElementById('preview-modal'));
                modal.show();
            } else {
                showAlert('danger', 'Failed to preview configuration');
            }
        } catch (error) {
            showAlert('danger', 'Preview failed: ' + error.message);
        }
    });

    // Execute provisioning
    executeBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to provision this router? This will modify its configuration.')) {
            executeProvisioning();
        }
    });

    document.getElementById('apply-from-preview').addEventListener('click', function() {
        // TODO: Replace Bootstrap modal with Alpine.js or custom Tailwind modal
        bootstrap.Modal.getInstance(document.getElementById('preview-modal')).hide();
        executeProvisioning();
    });

    async function executeProvisioning() {
        const variables = getVariables();
        progressSection.classList.remove('hidden');
        document.getElementById('progress-steps').innerHTML = '';
        executeBtn.disabled = true;

        try {
            const response = await fetch('{{ route("panel.admin.routers.provision.execute") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    router_id: selectedRouterId,
                    template_id: selectedTemplateId,
                    variables: variables
                })
            });

            const data = await response.json();
            
            if (data.success) {
                updateProgress(100, data.steps);
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 2000);
            } else {
                updateProgress(0, data.steps);
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Provisioning failed: ' + error.message);
        } finally {
            executeBtn.disabled = false;
        }
    }

    function getVariables() {
        return {
            central_server_ip: document.getElementById('var-central-server-ip').value,
            radius_server: document.getElementById('var-radius-server').value,
            radius_secret: document.getElementById('var-radius-secret').value,
            system_identity: document.getElementById('var-system-identity').value,
            hotspot_address: document.getElementById('var-hotspot-address').value,
            dns_name: document.getElementById('var-dns-name').value,
            pppoe_pool_start: document.getElementById('var-pppoe-pool-start').value,
            pppoe_pool_end: document.getElementById('var-pppoe-pool-end').value,
            timezone: document.getElementById('var-timezone').value,
            ntp_server: document.getElementById('var-ntp-server').value
        };
    }

    function updateProgress(percentage, steps) {
        const progressBar = document.getElementById('progress-bar');
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage + '%';

        const stepsContainer = document.getElementById('progress-steps');
        stepsContainer.innerHTML = '';

        steps.forEach(step => {
            const stepElement = document.createElement('div');
            stepElement.className = `p-3 bg-white border rounded ${step.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}`;
            stepElement.innerHTML = `
                <div class="flex justify-between items-center">
                    <span><i class="fas fa-${step.success ? 'check' : 'times'} mr-2"></i>${step.step}</span>
                    <small>${step.message}</small>
                </div>
            `;
            stepsContainer.appendChild(stepElement);
        });
    }

    // Create backup
    createBackupBtn.addEventListener('click', async function() {
        if (!selectedRouterId) return;

        const btn = this;
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("panel.admin.routers.provision.backup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ router_id: selectedRouterId })
            });

            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
            }
        } catch (error) {
            showAlert('danger', 'Backup failed: ' + error.message);
        } finally {
            btn.disabled = false;
        }
    });

    // Rollback handlers
    document.querySelectorAll('.rollback-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const backupId = this.dataset.backupId;
            
            if (!confirm('Are you sure you want to rollback to this backup? This will replace the current configuration.')) {
                return;
            }

            try {
                const response = await fetch('{{ route("panel.admin.routers.provision.rollback") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        router_id: selectedRouterId,
                        backup_id: backupId
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', data.message);
                }
            } catch (error) {
                showAlert('danger', 'Rollback failed: ' + error.message);
            }
        });
    });

    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
        alertDiv.className = `p-4 rounded-md mb-4 border ${bgColor} fixed top-0 left-1/2 -translate-x-1/2 mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = message;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // Trigger test connection if router is pre-selected
    if (routerSelect.value) {
        routerSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
