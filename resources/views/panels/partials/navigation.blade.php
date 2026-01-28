<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center lg:hidden">
                <!-- Mobile menu button -->
                <button id="mobileMenuToggle" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
            
            <div class="flex items-center flex-1 justify-between">
                <!-- Page Title / Breadcrumbs -->
                <div class="hidden lg:flex items-center">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        @yield('title', 'Dashboard')
                    </h2>
                </div>

                <!-- Right Side: User Info and Actions -->
                <div class="flex items-center space-x-4 ml-auto">
                    <!-- Language Switcher (Task 6.2) -->
                    <x-language-switcher />

                    <!-- Notifications Icon -->
                    <button class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- User Info -->
                    <div class="flex items-center">
                        <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300 mr-3">
                            {{ auth()->user()->name }}
                        </span>
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script nonce="{{ $cspNonce }}">
    // Connect mobile menu button to sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (mobileMenuToggle && sidebar && sidebarOverlay) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
            });
        }
    });
</script>
