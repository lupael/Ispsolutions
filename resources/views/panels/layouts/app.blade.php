<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ISP Solution') - {{ config('app.name', 'ISP Solution') }}</title>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="min-h-screen">
        <!-- Sidebar Navigation -->
        @include('panels.partials.sidebar')

        <!-- Main Content Area -->
        <div class="lg:ml-64">
            <!-- Top Navigation Bar -->
            @include('panels.partials.navigation')

            <!-- Impersonation Banner -->
            @if(session('impersonating'))
                <div class="bg-yellow-500 text-white shadow-lg">
                    <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between flex-wrap">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="font-semibold">
                                    You are currently impersonating: <strong>{{ session('impersonated_user_name') }}</strong>
                                </span>
                            </div>
                            <form action="{{ route('panel.admin.stop-impersonating') }}" method="POST" class="mt-2 sm:mt-0">
                                @csrf
                                <button type="submit" class="bg-white text-yellow-600 px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-50 transition duration-150">
                                    Return to Admin Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="py-6">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
        </div>
    </div>
    
    <!-- Scripts -->
    @stack('scripts')
</body>
</html>
