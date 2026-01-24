@props([
    'customerId',
    'autoRefresh' => false,
    'refreshInterval' => 300000, // 5 minutes in milliseconds
])

<div class="bandwidth-graph-container" data-customer-id="{{ $customerId }}">
    <!-- Timeframe Selector -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Bandwidth Usage</h3>
            <div class="flex gap-2">
                <button type="button" 
                        class="timeframe-btn px-3 py-1 text-sm rounded border active" 
                        data-timeframe="hourly">
                    Hourly
                </button>
                <button type="button" 
                        class="timeframe-btn px-3 py-1 text-sm rounded border" 
                        data-timeframe="daily">
                    Daily
                </button>
                <button type="button" 
                        class="timeframe-btn px-3 py-1 text-sm rounded border" 
                        data-timeframe="weekly">
                    Weekly
                </button>
                <button type="button" 
                        class="timeframe-btn px-3 py-1 text-sm rounded border" 
                        data-timeframe="monthly">
                    Monthly
                </button>
                @if($autoRefresh)
                <button type="button" 
                        id="refresh-toggle-{{ $customerId }}" 
                        class="px-3 py-1 text-sm rounded border bg-green-50 border-green-500 text-green-700"
                        title="Auto-refresh enabled">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Graph Display -->
    <div class="graph-display bg-white rounded-lg border border-gray-200 p-4">
        <div id="graph-loading-{{ $customerId }}" class="text-center py-8">
            <svg class="animate-spin h-8 w-8 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Loading graph...</p>
        </div>
        <div id="graph-error-{{ $customerId }}" class="hidden text-center py-8 text-red-600">
            <svg class="h-8 w-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p id="graph-error-message-{{ $customerId }}">Failed to load graph</p>
        </div>
        <img id="graph-image-{{ $customerId }}" class="hidden w-full" alt="Bandwidth Graph" />
    </div>

    <!-- Legend -->
    <div class="mt-4 flex items-center gap-6 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-500 rounded"></div>
            <span class="text-gray-700">Upload</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-blue-500 rounded"></div>
            <span class="text-gray-700">Download</span>
        </div>
    </div>
</div>

<style>
    .timeframe-btn {
        background: white;
        border-color: #d1d5db;
        color: #6b7280;
        transition: all 0.2s;
    }
    
    .timeframe-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    .timeframe-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
</style>

<script nonce="{{ $cspNonce }}">
(function() {
    const customerId = @json($customerId);
    const autoRefresh = @json((bool) $autoRefresh);
    const refreshInterval = @json((int) $refreshInterval);
    let currentTimeframe = 'hourly';
    let refreshTimer = null;
    let autoRefreshEnabled = autoRefresh;

    // Get elements
    const loadingEl = document.getElementById(`graph-loading-${customerId}`);
    const errorEl = document.getElementById(`graph-error-${customerId}`);
    const errorMessageEl = document.getElementById(`graph-error-message-${customerId}`);
    const imageEl = document.getElementById(`graph-image-${customerId}`);
    const timeframeBtns = document.querySelectorAll(`[data-customer-id="${customerId}"] .timeframe-btn`);
    const refreshToggle = document.getElementById(`refresh-toggle-${customerId}`);

    // Load graph function
    function loadGraph(timeframe) {
        currentTimeframe = timeframe;
        
        // Show loading
        loadingEl.classList.remove('hidden');
        errorEl.classList.add('hidden');
        imageEl.classList.add('hidden');

        // Make API request
        fetch(`/api/v1/customers/${customerId}/graphs/${timeframe}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data && data.data.graph) {
                // Display graph
                imageEl.src = `data:image/png;base64,${data.data.graph}`;
                imageEl.classList.remove('hidden');
                loadingEl.classList.add('hidden');
            } else {
                throw new Error(data.message || 'Invalid response format');
            }
        })
        .catch(error => {
            console.error('Failed to load graph:', error);
            errorMessageEl.textContent = `Failed to load graph: ${error.message}`;
            errorEl.classList.remove('hidden');
            loadingEl.classList.add('hidden');
        });
    }

    // Handle timeframe button clicks
    timeframeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const timeframe = this.getAttribute('data-timeframe');
            
            // Update active state
            timeframeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Load graph
            loadGraph(timeframe);
            
            // Restart auto-refresh timer
            if (autoRefreshEnabled) {
                restartAutoRefresh();
            }
        });
    });

    // Handle refresh toggle
    if (refreshToggle) {
        refreshToggle.addEventListener('click', function() {
            autoRefreshEnabled = !autoRefreshEnabled;
            
            if (autoRefreshEnabled) {
                this.classList.add('bg-green-50', 'border-green-500', 'text-green-700');
                this.classList.remove('bg-gray-50', 'border-gray-300', 'text-gray-500');
                this.title = 'Auto-refresh enabled';
                restartAutoRefresh();
            } else {
                this.classList.add('bg-gray-50', 'border-gray-300', 'text-gray-500');
                this.classList.remove('bg-green-50', 'border-green-500', 'text-green-700');
                this.title = 'Auto-refresh disabled';
                stopAutoRefresh();
            }
        });
    }

    // Auto-refresh functions
    function restartAutoRefresh() {
        stopAutoRefresh();
        if (autoRefreshEnabled) {
            refreshTimer = setInterval(() => {
                loadGraph(currentTimeframe);
            }, refreshInterval);
        }
    }

    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    // Initial load
    loadGraph(currentTimeframe);

    // Start auto-refresh if enabled
    if (autoRefreshEnabled) {
        restartAutoRefresh();
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', stopAutoRefresh);
})();
</script>
