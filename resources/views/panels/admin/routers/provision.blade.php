@extends('layouts.app')

@section('title', 'Zero-Touch Router Provisioning')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Zero-Touch Router Provisioning
                    </h5>
                    <div>
                        <a href="{{ route('admin.routers.provision.templates') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-code me-1"></i>Manage Templates
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Router Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="router-select" class="form-label fw-bold">Select Router</label>
                            <select id="router-select" class="form-select">
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
                        <div class="col-md-6 d-flex align-items-end">
                            <button id="test-connection" class="btn btn-outline-info" disabled>
                                <i class="fas fa-network-wired me-1"></i>Test Connection
                            </button>
                        </div>
                    </div>

                    <!-- Router Info Panel -->
                    <div id="router-info" class="alert alert-info d-none mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Router:</strong> <span id="router-name"></span><br>
                                <strong>IP:</strong> <span id="router-ip"></span><br>
                                <strong>Status:</strong> <span id="router-status" class="badge"></span>
                            </div>
                            <div>
                                <button id="create-backup" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-save me-1"></i>Create Backup
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Template Selection & Variables -->
                    <div id="provisioning-form" class="d-none">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="template-select" class="form-label fw-bold">Configuration Template</label>
                                <select id="template-select" class="form-select">
                                    <option value="">-- Select a Template --</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" data-type="{{ $template->template_type }}">
                                            {{ $template->name }} ({{ ucfirst(str_replace('_', ' ', $template->template_type)) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select a pre-configured template or create a custom one</small>
                            </div>
                        </div>

                        <!-- Configuration Variables -->
                        <div id="variables-section" class="d-none">
                            <h6 class="mb-3">Configuration Variables</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="var-central-server-ip" class="form-label">Central Server IP</label>
                                    <input type="text" id="var-central-server-ip" class="form-control" 
                                           placeholder="192.168.1.100" value="{{ request()->getHost() }}">
                                    <small class="text-muted">IP address of the central management server</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-radius-server" class="form-label">RADIUS Server</label>
                                    <input type="text" id="var-radius-server" class="form-control" 
                                           placeholder="127.0.0.1" value="127.0.0.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-radius-secret" class="form-label">RADIUS Secret</label>
                                    <input type="text" id="var-radius-secret" class="form-control" 
                                           placeholder="mysecretkey" value="testing123">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-system-identity" class="form-label">System Identity</label>
                                    <input type="text" id="var-system-identity" class="form-control" 
                                           placeholder="ISP-Router-01">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-hotspot-address" class="form-label">Hotspot Address</label>
                                    <input type="text" id="var-hotspot-address" class="form-control" 
                                           placeholder="10.5.50.1" value="10.5.50.1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-dns-name" class="form-label">Hotspot DNS Name</label>
                                    <input type="text" id="var-dns-name" class="form-control" 
                                           placeholder="hotspot.local" value="hotspot.local">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-pppoe-pool-start" class="form-label">PPPoE Pool Start</label>
                                    <input type="text" id="var-pppoe-pool-start" class="form-control" 
                                           placeholder="10.0.0.2" value="10.0.0.2">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-pppoe-pool-end" class="form-label">PPPoE Pool End</label>
                                    <input type="text" id="var-pppoe-pool-end" class="form-control" 
                                           placeholder="10.0.0.254" value="10.0.0.254">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-timezone" class="form-label">Timezone</label>
                                    <input type="text" id="var-timezone" class="form-control" 
                                           placeholder="Asia/Kolkata" value="UTC">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="var-ntp-server" class="form-label">NTP Server</label>
                                    <input type="text" id="var-ntp-server" class="form-control" 
                                           placeholder="pool.ntp.org" value="pool.ntp.org">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button id="preview-config" class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-eye me-1"></i>Preview Configuration
                            </button>
                            <button id="execute-provision" class="btn btn-primary" disabled>
                                <i class="fas fa-play me-1"></i>Execute Provisioning
                            </button>
                        </div>
                    </div>

                    <!-- Progress Section -->
                    <div id="progress-section" class="d-none mt-4">
                        <h6>Provisioning Progress</h6>
                        <div class="progress mb-3" style="height: 30px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <div id="progress-steps" class="list-group">
                            <!-- Steps will be added dynamically -->
                        </div>
                    </div>

                    <!-- Configuration Preview Modal -->
                    <div class="modal fade" id="preview-modal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Configuration Preview</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <pre id="config-preview" class="bg-light p-3 rounded" style="max-height: 500px; overflow-y: auto;"></pre>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="apply-from-preview">Apply Configuration</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Logs & Backups -->
                    @if(isset($provisioningLogs) && $provisioningLogs->count() > 0)
                    <div class="mt-5">
                        <h6>Recent Provisioning Logs</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <th>Template</th>
                                        <th>Status</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($provisioningLogs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ ucfirst($log->action) }}</td>
                                        <td>{{ $log->template->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $log->user->name ?? 'System' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if(isset($backups) && $backups->count() > 0)
                    <div class="mt-4">
                        <h6>Configuration Backups</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                    <tr>
                                        <td>{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ ucfirst($backup->backup_type) }}</td>
                                        <td>{{ $backup->creator->name ?? 'System' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning rollback-btn" 
                                                    data-backup-id="{{ $backup->id }}">
                                                <i class="fas fa-undo me-1"></i>Rollback
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
<script>
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
            routerInfo.classList.remove('d-none');
            provisioningForm.classList.remove('d-none');
        } else {
            testConnectionBtn.disabled = true;
            routerInfo.classList.add('d-none');
            provisioningForm.classList.add('d-none');
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
            const response = await fetch('{{ route("admin.routers.provision.test-connection") }}', {
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
                statusBadge.className = 'badge bg-success';
                statusBadge.textContent = 'Connected';
                showAlert('success', data.message);
            } else {
                statusBadge.className = 'badge bg-danger';
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
            variablesSection.classList.remove('d-none');
            previewBtn.disabled = false;
            executeBtn.disabled = false;
            
            // Set default system identity based on router name
            const routerName = document.getElementById('router-name').textContent;
            document.getElementById('var-system-identity').value = routerName.split(' ')[0] || 'ISP-Router';
        } else {
            variablesSection.classList.add('d-none');
            previewBtn.disabled = true;
            executeBtn.disabled = true;
        }
    });

    // Preview configuration
    previewBtn.addEventListener('click', async function() {
        const variables = getVariables();
        
        try {
            const response = await fetch('{{ route("admin.routers.provision.preview") }}', {
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
        bootstrap.Modal.getInstance(document.getElementById('preview-modal')).hide();
        executeProvisioning();
    });

    async function executeProvisioning() {
        const variables = getVariables();
        progressSection.classList.remove('d-none');
        document.getElementById('progress-steps').innerHTML = '';
        executeBtn.disabled = true;

        try {
            const response = await fetch('{{ route("admin.routers.provision.execute") }}', {
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
            stepElement.className = `list-group-item ${step.success ? 'list-group-item-success' : 'list-group-item-danger'}`;
            stepElement.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-${step.success ? 'check' : 'times'} me-2"></i>${step.step}</span>
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
            const response = await fetch('{{ route("admin.routers.provision.backup") }}', {
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
                const response = await fetch('{{ route("admin.routers.provision.rollback") }}', {
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
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
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
