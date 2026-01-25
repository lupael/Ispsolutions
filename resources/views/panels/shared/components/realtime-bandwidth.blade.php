@props([
    'customerId',
    'refreshInterval' => 5000, // 5 seconds in milliseconds
])

<div class="realtime-bandwidth-container" data-customer-id="{{ $customerId }}">
    <!-- Header -->
    <div class="mb-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Real-Time Bandwidth</h3>
            <div class="flex items-center gap-2">
                <span id="status-indicator-{{ $customerId }}" class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    <span>Connecting...</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Bandwidth Display -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Upload Speed -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg border border-green-200 dark:border-green-700 p-6">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-green-900 dark:text-green-100">Upload</h4>
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
            </div>
            <div id="upload-speed-{{ $customerId }}" class="text-3xl font-bold text-green-700 dark:text-green-300">
                0 KB/s
            </div>
            <div class="mt-2 text-xs text-green-600 dark:text-green-400">
                Total: <span id="upload-total-{{ $customerId }}">0 MB</span>
            </div>
        </div>

        <!-- Download Speed -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg border border-blue-200 dark:border-blue-700 p-6">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Download</h4>
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                </svg>
            </div>
            <div id="download-speed-{{ $customerId }}" class="text-3xl font-bold text-blue-700 dark:text-blue-300">
                0 KB/s
            </div>
            <div class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                Total: <span id="download-total-{{ $customerId }}">0 MB</span>
            </div>
        </div>
    </div>

    <!-- Session Information -->
    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                <span id="session-status-{{ $customerId }}" class="ml-2 font-medium text-gray-900 dark:text-gray-100">-</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">Session Time:</span>
                <span id="session-time-{{ $customerId }}" class="ml-2 font-medium text-gray-900 dark:text-gray-100">-</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">IP Address:</span>
                <span id="session-ip-{{ $customerId }}" class="ml-2 font-medium text-gray-900 dark:text-gray-100">-</span>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">NAS:</span>
                <span id="session-nas-{{ $customerId }}" class="ml-2 font-medium text-gray-900 dark:text-gray-100">-</span>
            </div>
        </div>
    </div>

    <!-- Loading/Error States -->
    <div id="bandwidth-loading-{{ $customerId }}" class="hidden text-center py-4">
        <svg class="animate-spin h-6 w-6 mx-auto text-blue-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    <div id="bandwidth-error-{{ $customerId }}" class="hidden text-center py-4 text-red-600 dark:text-red-400">
        <p class="text-sm">Unable to fetch bandwidth data</p>
    </div>
</div>

<script nonce="{{ $cspNonce }}">
(function() {
    const customerId = @json($customerId);
    const refreshInterval = @json((int) $refreshInterval);
    let refreshTimer = null;
    let previousData = { upload: 0, download: 0, timestamp: Date.now() };

    // Get elements
    const statusIndicator = document.getElementById(`status-indicator-${customerId}`);
    const uploadSpeed = document.getElementById(`upload-speed-${customerId}`);
    const downloadSpeed = document.getElementById(`download-speed-${customerId}`);
    const uploadTotal = document.getElementById(`upload-total-${customerId}`);
    const downloadTotal = document.getElementById(`download-total-${customerId}`);
    const sessionStatus = document.getElementById(`session-status-${customerId}`);
    const sessionTime = document.getElementById(`session-time-${customerId}`);
    const sessionIp = document.getElementById(`session-ip-${customerId}`);
    const sessionNas = document.getElementById(`session-nas-${customerId}`);

    // Format bytes to human readable
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Format speed (bytes per second)
    function formatSpeed(bytesPerSecond) {
        if (bytesPerSecond === 0) return '0 KB/s';
        
        const k = 1024;
        if (bytesPerSecond < k) {
            return Math.round(bytesPerSecond) + ' B/s';
        } else if (bytesPerSecond < k * k) {
            return (bytesPerSecond / k).toFixed(2) + ' KB/s';
        } else {
            return (bytesPerSecond / k / k).toFixed(2) + ' MB/s';
        }
    }

    // Format session time
    function formatSessionTime(seconds) {
        if (!seconds) return '-';
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        
        return `${hours}h ${minutes}m ${secs}s`;
    }

    // Update status indicator
    function updateStatus(status, message) {
        const dot = statusIndicator.querySelector('.w-2');
        const text = statusIndicator.querySelector('span:last-child');
        
        if (status === 'active') {
            dot.className = 'w-2 h-2 rounded-full bg-green-500 animate-pulse';
            text.textContent = message || 'Active';
        } else if (status === 'error') {
            dot.className = 'w-2 h-2 rounded-full bg-red-500';
            text.textContent = message || 'Error';
        } else {
            dot.className = 'w-2 h-2 rounded-full bg-gray-400';
            text.textContent = message || 'Inactive';
        }
    }

    // Fetch bandwidth data
    function fetchBandwidthData() {
        fetch(`/api/v1/radius/users/${customerId}/realtime-stats`, {
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
            if (data.success && data.data) {
                updateBandwidthDisplay(data.data);
                updateStatus('active', 'Live');
            } else {
                throw new Error(data.message || 'Invalid response format');
            }
        })
        .catch(error => {
            console.error('Failed to fetch bandwidth data:', error);
            updateStatus('error', 'Connection Error');
        });
    }

    // Update bandwidth display
    function updateBandwidthDisplay(data) {
        const now = Date.now();
        
        // Update totals immediately
        document.getElementById(`upload-total-${customerId}`).textContent = formatBytes(data.upload);
        document.getElementById(`download-total-${customerId}`).textContent = formatBytes(data.download);

        // Calculate speed only after first poll
        if (isFirstPoll || previousData === null) {
            // First poll - establish baseline, don't calculate speed yet
            isFirstPoll = false;
            previousData = {
                upload: data.upload,
                download: data.download,
                timestamp: now
            };
            
            // Show 0 speed on first poll
            document.getElementById(`upload-speed-${customerId}`).textContent = '0 KB/s';
            document.getElementById(`download-speed-${customerId}`).textContent = '0 KB/s';
        } else {
            const timeDiff = (now - previousData.timestamp) / 1000; // seconds

            // Check for counter reset (session restart or offline)
            if (data.upload < previousData.upload || data.download < previousData.download) {
                // Counter reset detected - reset baseline
                previousData = {
                    upload: data.upload,
                    download: data.download,
                    timestamp: now
                };
                document.getElementById(`upload-speed-${customerId}`).textContent = '0 KB/s';
                document.getElementById(`download-speed-${customerId}`).textContent = '0 KB/s';
            } else if (timeDiff > 0) {
                // Calculate speed (bytes per second)
                const uploadSpeed = (data.upload - previousData.upload) / timeDiff;
                const downloadSpeed = (data.download - previousData.download) / timeDiff;
                
                // Update speed displays
                document.getElementById(`upload-speed-${customerId}`).textContent = formatSpeed(uploadSpeed);
                document.getElementById(`download-speed-${customerId}`).textContent = formatSpeed(downloadSpeed);
                
                // Store current data for next calculation
                previousData = {
                    upload: data.upload,
                    download: data.download,
                    timestamp: now
                };
            }
        }

        // Update session information
        document.getElementById(`session-status-${customerId}`).textContent = data.status || '-';
        document.getElementById(`session-time-${customerId}`).textContent = formatSessionTime(data.session_time);
        document.getElementById(`session-ip-${customerId}`).textContent = data.ip_address || '-';
        document.getElementById(`session-nas-${customerId}`).textContent = data.nas_identifier || '-';
    }

    // Start auto-refresh
    function startAutoRefresh() {
        // Initial fetch
        fetchBandwidthData();
        
        // Set up periodic refresh
        refreshTimer = setInterval(() => {
            fetchBandwidthData();
        }, refreshInterval);
    }

    // Stop auto-refresh
    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    // Initialize
    startAutoRefresh();

    // Cleanup on page unload
    window.addEventListener('beforeunload', stopAutoRefresh);
})();
</script>
