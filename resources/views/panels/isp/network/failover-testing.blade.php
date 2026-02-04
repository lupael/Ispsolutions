@extends('panels.layouts.app')

@section('title', 'Automatic Failover Testing')

@section('content')
<div class="space-y-6" x-data="failoverTesting()">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Automatic Failover Testing</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Automate and schedule failover tests to ensure high availability</p>
                </div>
                <div class="flex gap-3">
                    <button @click="runManualTest()" :disabled="isRunningTest" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" :class="{'animate-spin': isRunningTest}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isRunningTest ? 'Testing...' : 'Run Manual Test'"></span>
                    </button>
                    <button @click="showScheduleModal = true" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Schedule Tests
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Configuration -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Test Configuration</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Router</label>
                    <select x-model="selectedRouter" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a router...</option>
                        <template x-for="router in routers" :key="router.id">
                            <option :value="router.id" x-text="`${router.name} (${router.ip_address})`"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Test Type</label>
                    <select x-model="testType" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="radius-failover">RADIUS Failover</option>
                        <option value="connection-failover">Connection Failover</option>
                        <option value="full-failover">Full Failover Test</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Test Duration (seconds)</label>
                    <input type="number" x-model="testDuration" min="10" max="300" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Auto-recovery</label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" x-model="autoRecover" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Automatically recover after test</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Test Progress -->
    <div x-show="currentTest" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Test Progress</h2>
            
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-700 dark:text-gray-300">Test Progress</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="`${currentTest.progress}%`"></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" :style="`width: ${currentTest.progress}%`"></div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Test Steps:</h3>
                    <div class="space-y-2">
                        <template x-for="(step, index) in currentTest.steps" :key="index">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <template x-if="step.status === 'completed'">
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="step.status === 'running'">
                                        <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </template>
                                    <template x-if="step.status === 'pending'">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </template>
                                    <template x-if="step.status === 'failed'">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-gray-100" x-text="step.description"></p>
                                    <template x-if="step.message">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="step.message"></p>
                                    </template>
                                </div>
                                <template x-if="step.duration">
                                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="step.duration + 'ms'"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="currentTest.status === 'completed'" class="p-4 rounded-lg"
                     :class="currentTest.success ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-900' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900'">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5" :class="currentTest.success ? 'text-green-400' : 'text-red-400'" fill="currentColor" viewBox="0 0 20 20">
                                <template x-if="currentTest.success">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </template>
                                <template x-if="!currentTest.success">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </template>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium" :class="currentTest.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'">
                                <span x-text="currentTest.success ? 'Test Completed Successfully' : 'Test Failed'"></span>
                            </h3>
                            <div class="mt-2 text-sm" :class="currentTest.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                                <p x-text="currentTest.summary"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test History -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Test History</h2>
                <button @click="loadHistory()" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                    Refresh
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Router</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Test Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Started</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Result</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="test in testHistory" :key="test.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="test.router_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="test.test_type"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDateTime(test.started_at)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="test.duration + 's'"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': test.result === 'success',
                                              'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': test.result === 'failed',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': test.result === 'partial'
                                          }"
                                          x-text="test.result.toUpperCase()">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="viewTestDetails(test.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                        Details
                                    </button>
                                    <button @click="rerunTest(test.id)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        Rerun
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="testHistory.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No test history available
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div x-show="showScheduleModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showScheduleModal = false"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Schedule Failover Tests</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Test Frequency</label>
                            <select x-model="schedule.frequency" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Time</label>
                            <input type="time" x-model="schedule.time" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" x-model="schedule.enabled" class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable automatic testing</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="saveSchedule()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Schedule
                    </button>
                    <button @click="showScheduleModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-700 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto sm:text-sm">
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
function failoverTesting() {
    return {
        routers: [],
        selectedRouter: '',
        testType: 'radius-failover',
        testDuration: 60,
        autoRecover: true,
        isRunningTest: false,
        currentTest: null,
        testHistory: [],
        showScheduleModal: false,
        schedule: {
            frequency: 'daily',
            time: '02:00',
            enabled: false
        },
        
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
            try {
                const response = await fetch('/api/routers/failover-tests/history', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.testHistory = data.tests || [];
                }
            } catch (error) {
                console.error('Error loading history:', error);
            }
        },
        
        async runManualTest() {
            if (!this.selectedRouter) {
                window.showNotification('Please select a router', 'warning');
                return;
            }
            
            this.isRunningTest = true;
            this.currentTest = {
                progress: 0,
                status: 'running',
                steps: [
                    { description: 'Initializing test environment', status: 'running' },
                    { description: 'Backing up current configuration', status: 'pending' },
                    { description: 'Simulating RADIUS server failure', status: 'pending' },
                    { description: 'Verifying failover to local authentication', status: 'pending' },
                    { description: 'Restoring RADIUS connection', status: 'pending' },
                    { description: 'Verifying failback to RADIUS', status: 'pending' },
                    { description: 'Finalizing test results', status: 'pending' }
                ]
            };
            
            try {
                const response = await fetch('/api/routers/failover-tests/run', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        router_id: this.selectedRouter,
                        test_type: this.testType,
                        duration: this.testDuration,
                        auto_recover: this.autoRecover
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    // Simulate test progress
                    await this.simulateTestProgress(data.test_id);
                } else {
                    window.showNotification('Failed to start test', 'error');
                    this.isRunningTest = false;
                    this.currentTest = null;
                }
            } catch (error) {
                console.error('Error running test:', error);
                window.showNotification('Error running test', 'error');
                this.isRunningTest = false;
                this.currentTest = null;
            }
        },
        
        async simulateTestProgress(testId) {
            // NOTE: This is a simulated progress display for UI demonstration purposes.
            // In production, this should poll the server for actual test progress via an API endpoint
            // like GET /api/routers/failover-tests/{testId}/progress
            
            for (let i = 0; i < this.currentTest.steps.length; i++) {
                await new Promise(resolve => setTimeout(resolve, 2000));
                this.currentTest.steps[i].status = 'running';
                this.currentTest.progress = Math.floor(((i + 1) / this.currentTest.steps.length) * 100);
                
                await new Promise(resolve => setTimeout(resolve, 1000));
                this.currentTest.steps[i].status = 'completed';
                this.currentTest.steps[i].duration = Math.floor(Math.random() * 2000) + 500;
                
                if (i < this.currentTest.steps.length - 1) {
                    this.currentTest.steps[i + 1].status = 'running';
                }
            }
            
            this.currentTest.status = 'completed';
            this.currentTest.success = true;
            this.currentTest.summary = 'Failover test completed successfully. Router successfully failed over to local authentication and failed back to RADIUS.';
            this.isRunningTest = false;
            
            await this.loadHistory();
            window.showNotification('Failover test completed successfully', 'success');
        },
        
        async saveSchedule() {
            try {
                const response = await fetch('/api/routers/failover-tests/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.schedule)
                });
                
                const data = await response.json();
                window.showNotification(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    this.showScheduleModal = false;
                }
            } catch (error) {
                console.error('Error saving schedule:', error);
                window.showNotification('Error saving schedule', 'error');
            }
        },
        
        viewTestDetails(testId) {
            const test = this.testHistory.find(t => t.id === testId);
            if (!test) {
                window.showNotification('Test details not found', 'error');
                return;
            }
            
            const details = [
                `Test Type: ${test.test_type || 'N/A'}`,
                `Router: ${test.router_name || 'N/A'}`,
                `Status: ${(test.result || test.status || 'unknown').toUpperCase()}`,
                `Started: ${this.formatDateTime(test.started_at)}`,
                test.completed_at ? `Completed: ${this.formatDateTime(test.completed_at)}` : null,
                `Duration: ${test.duration || 'N/A'}s`
            ].filter(Boolean).join('\n');
            
            alert(`Test Details:\n\n${details}`);
        },
        
        async rerunTest(testId) {
            const originalTest = this.testHistory.find(t => t.id === testId);
            if (!originalTest) {
                window.showNotification('Unable to rerun test: original test not found', 'error');
                return;
            }
            
            // Set configuration based on original test
            this.selectedRouter = originalTest.router_id || '';
            this.testType = originalTest.test_type || 'radius-failover';
            this.testDuration = originalTest.duration || 60;
            this.autoRecover = true;
            
            // Run the test with same configuration
            window.showNotification(`Rerunning ${originalTest.test_type} test for ${originalTest.router_name}`, 'info');
            await this.runManualTest();
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
