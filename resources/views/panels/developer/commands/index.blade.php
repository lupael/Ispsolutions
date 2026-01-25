@extends('panels.layouts.app')

@section('title', 'Command Execution')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h1 class="text-3xl font-bold">Command Execution Panel</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Execute whitelisted artisan and system commands</p>
            <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            <strong>Security Notice:</strong> Only whitelisted commands can be executed. Dangerous operations are blocked.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Artisan Commands -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Laravel Artisan Commands</h2>
            
            <div class="space-y-4">
                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($artisanCommands as $command => $description)
                        <button 
                            onclick="executeArtisanCommand('{{ $command }}')"
                            class="text-left p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $command }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $description }}</div>
                        </button>
                    @endforeach
                </div>

                <!-- Custom Artisan Command -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Custom Artisan Command
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="customArtisanCommand"
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g., cache:clear">
                        <button 
                            onclick="executeCustomArtisan()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                            Execute
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Commands -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">System Commands</h2>
            
            <div class="space-y-4">
                <!-- Quick System Commands -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($systemCommands as $command => $description)
                        <button 
                            onclick="executeSystemCommand('{{ $command }}')"
                            class="text-left p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $command }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $description }}</div>
                        </button>
                    @endforeach
                </div>

                <!-- Custom System Command -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Custom System Command
                    </label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="customSystemCommand"
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="e.g., ping 8.8.8.8 -c 4">
                        <button 
                            onclick="executeCustomSystem()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                            Execute
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Output Console -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Command Output</h2>
                <button 
                    onclick="clearOutput()"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                    Clear
                </button>
            </div>
            <div id="commandOutput" class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm h-96 overflow-auto">
                <div class="text-gray-500">Ready. Execute a command to see output...</div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';

    function addOutput(text, type = 'info') {
        const output = document.getElementById('commandOutput');
        const timestamp = new Date().toLocaleTimeString();
        const colors = {
            info: 'text-green-400',
            error: 'text-red-400',
            warning: 'text-yellow-400',
            success: 'text-cyan-400'
        };
        
        const line = document.createElement('div');
        line.className = colors[type] || colors.info;
        line.innerHTML = `<span class="text-gray-500">[${timestamp}]</span> ${escapeHtml(text)}`;
        output.appendChild(line);
        output.scrollTop = output.scrollHeight;
    }

    function clearOutput() {
        document.getElementById('commandOutput').innerHTML = '<div class="text-gray-500">Ready. Execute a command to see output...</div>';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function executeArtisanCommand(command) {
        addOutput(`Executing: php artisan ${command}`, 'info');
        
        try {
            const response = await fetch('{{ route('panel.developer.commands.execute-artisan') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ command })
            });

            const data = await response.json();

            if (data.success) {
                addOutput('Command completed successfully!', 'success');
                if (data.output) {
                    addOutput(data.output, 'info');
                }
            } else {
                addOutput('Error: ' + data.error, 'error');
            }
        } catch (error) {
            addOutput('Network error: ' + error.message, 'error');
        }
    }

    async function executeSystemCommand(command) {
        addOutput(`Executing: ${command}`, 'info');
        
        try {
            const response = await fetch('{{ route('panel.developer.commands.execute-system') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ command })
            });

            const data = await response.json();

            if (data.success) {
                addOutput('Command completed successfully!', 'success');
                if (data.output) {
                    data.output.split('\n').forEach(line => {
                        if (line.trim()) addOutput(line, 'info');
                    });
                }
            } else {
                addOutput('Error: ' + (data.error || 'Command failed'), 'error');
                if (data.error_output) {
                    data.error_output.split('\n').forEach(line => {
                        if (line.trim()) addOutput(line, 'error');
                    });
                }
            }
        } catch (error) {
            addOutput('Network error: ' + error.message, 'error');
        }
    }

    function executeCustomArtisan() {
        const input = document.getElementById('customArtisanCommand');
        const command = input.value.trim();
        
        if (!command) {
            addOutput('Please enter a command', 'warning');
            return;
        }
        
        executeArtisanCommand(command);
        input.value = '';
    }

    function executeCustomSystem() {
        const input = document.getElementById('customSystemCommand');
        const command = input.value.trim();
        
        if (!command) {
            addOutput('Please enter a command', 'warning');
            return;
        }
        
        executeSystemCommand(command);
        input.value = '';
    }

    // Allow enter key to execute
    document.getElementById('customArtisanCommand').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') executeCustomArtisan();
    });

    document.getElementById('customSystemCommand').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') executeCustomSystem();
    });
</script>
@endsection
