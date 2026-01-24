@extends('layouts.guest')

@section('title', 'Public Access Dashboard')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div>
            <div class="mx-auto h-12 w-auto flex items-center justify-center">
                <svg class="h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Public Access Dashboard
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                You are connected via temporary access link
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Status Card -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Connection Status
                </h3>

                <div class="space-y-4">
                    <!-- Status -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                        <span class="flex items-center">
                            <span class="h-2 w-2 bg-green-400 rounded-full mr-2"></span>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">Connected</span>
                        </span>
                    </div>

                    <!-- Session ID -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Session ID</span>
                        <span class="text-sm text-gray-900 dark:text-white font-mono">
                            {{ Str::limit($session_id, 12) }}
                        </span>
                    </div>

                    <!-- Expires At -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Expires At</span>
                        <span class="text-sm text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::createFromTimestamp($expires_at)->format('M d, Y h:i A') }}
                        </span>
                    </div>

                    <!-- Time Remaining -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Remaining</span>
                        <span class="text-sm text-gray-900 dark:text-white" id="time-remaining">
                            Calculating...
                        </span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-6">
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block text-blue-600 dark:text-blue-400">
                                    Session Progress
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-blue-600 dark:text-blue-400" id="progress-percent">
                                    0%
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200 dark:bg-blue-900">
                            <div id="progress-bar" style="width:0%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-600 transition-all duration-500"></div>
                        </div>
                    </div>
                </div>

                <!-- Information -->
                <div class="mt-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-blue-700 dark:text-blue-200">
                                You are using a temporary access link. Your connection will automatically expire at the scheduled time.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('hotspot.logout') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Disconnect
                    </button>
                </form>
            </div>
        </div>

        <!-- Features -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Available Features
                </h3>
                <ul class="space-y-3">
                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Internet Access
                    </li>
                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        No Authentication Required
                    </li>
                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Limited Time Access
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Calculate and update time remaining
    const expiresAt = {{ $expires_at }};
    const startTime = {{ $logged_in_at }}; // Use actual login time from session
    
    function updateTimeRemaining() {
        const now = Date.now() / 1000;
        const remaining = expiresAt - now;
        
        if (remaining <= 0) {
            document.getElementById('time-remaining').textContent = 'Expired';
            document.getElementById('progress-bar').style.width = '100%';
            document.getElementById('progress-percent').textContent = '100%';
            
            // Redirect to login page
            setTimeout(() => {
                window.location.href = "{{ route('hotspot.login') }}";
            }, 2000);
            return;
        }
        
        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = Math.floor(remaining % 60);
        
        let timeString = '';
        if (hours > 0) {
            timeString = `${hours}h ${minutes}m ${seconds}s`;
        } else if (minutes > 0) {
            timeString = `${minutes}m ${seconds}s`;
        } else {
            timeString = `${seconds}s`;
        }
        
        document.getElementById('time-remaining').textContent = timeString;
        
        // Update progress bar (calculate based on actual login time)
        const totalDuration = expiresAt - startTime;
        
        // Avoid division by zero
        if (totalDuration > 0) {
            const elapsed = now - startTime;
            const progress = Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
            
            document.getElementById('progress-bar').style.width = progress + '%';
            document.getElementById('progress-percent').textContent = Math.round(progress) + '%';
        }
        
        // Change color to red when less than 5 minutes remaining
        if (remaining < 300) {
            document.getElementById('progress-bar').classList.remove('bg-blue-600');
            document.getElementById('progress-bar').classList.add('bg-red-600');
        }
    }
    
    // Update every second
    updateTimeRemaining();
    setInterval(updateTimeRemaining, 1000);
</script>
@endsection
