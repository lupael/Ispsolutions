@extends('panels.layouts.app')

@section('title', 'Network Ping Test')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Network Ping Test Tool</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Test connectivity and latency to network devices</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form class="space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="target_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Target Host</label>
                        <input type="text" id="target_host" name="target_host" placeholder="192.168.1.1 or example.com" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter IP address or hostname</p>
                    </div>

                    <div>
                        <label for="ping_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number of Pings</label>
                        <select id="ping_count" name="ping_count" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="4">4</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div>
                        <label for="packet_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Packet Size (bytes)</label>
                        <select id="packet_size" name="packet_size" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="32">32</option>
                            <option value="64" selected>64</option>
                            <option value="128">128</option>
                            <option value="256">256</option>
                            <option value="512">512</option>
                            <option value="1024">1024</option>
                        </select>
                    </div>

                    <div>
                        <label for="timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timeout (seconds)</label>
                        <input type="number" id="timeout" name="timeout" value="5" min="1" max="30" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex justify-start space-x-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Start Ping Test
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Stop
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Access Devices -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Access Devices</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <button class="flex items-center p-4 border border-gray-300 dark:border-gray-700 rounded-lg hover:border-indigo-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <div class="ml-4 text-left">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Router 1</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">192.168.1.1</p>
                    </div>
                </button>

                <button class="flex items-center p-4 border border-gray-300 dark:border-gray-700 rounded-lg hover:border-indigo-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex-shrink-0 h-10 w-10 bg-purple-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="ml-4 text-left">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">OLT-Main</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">10.0.0.1</p>
                    </div>
                </button>

                <button class="flex items-center p-4 border border-gray-300 dark:border-gray-700 rounded-lg hover:border-indigo-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex-shrink-0 h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <div class="ml-4 text-left">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">DNS Server</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">8.8.8.8</p>
                    </div>
                </button>

                <button class="flex items-center p-4 border border-gray-300 dark:border-gray-700 rounded-lg hover:border-indigo-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <div class="ml-4 text-left">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Google</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">google.com</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Test Results</h3>
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Packets Sent</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">10</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Packets Received</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">10</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Packet Loss</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">0%</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Avg Latency</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">15ms</p>
                </div>
            </div>

            <!-- Detailed Results -->
            <div class="bg-gray-900 dark:bg-black rounded-lg p-4 font-mono text-sm text-green-400 overflow-x-auto">
                <pre id="ping-output" class="whitespace-pre-wrap">Ping test results will appear here...

To start a test:
1. Enter target host (IP address or hostname)
2. Configure test parameters
3. Click "Start Ping Test"

Example output:
PING google.com (142.250.185.46): 56 data bytes
64 bytes from 142.250.185.46: icmp_seq=0 ttl=117 time=14.2 ms
64 bytes from 142.250.185.46: icmp_seq=1 ttl=117 time=13.8 ms
64 bytes from 142.250.185.46: icmp_seq=2 ttl=117 time=15.1 ms
64 bytes from 142.250.185.46: icmp_seq=3 ttl=117 time=14.5 ms

--- google.com ping statistics ---
4 packets transmitted, 4 packets received, 0.0% packet loss
round-trip min/avg/max/stddev = 13.8/14.4/15.1/0.5 ms</pre>
            </div>
        </div>
    </div>
</div>
@endsection
