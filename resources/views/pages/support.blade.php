@extends('layouts.public')

@section('title', 'Support')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Support & Contact</h1>
    
    <div class="prose dark:prose-invert max-w-none">
        <p class="text-gray-700 dark:text-gray-300 mb-8">
            We're here to help! If you need assistance with our services, please reach out to us using one of the methods below.
        </p>

        <div class="grid md:grid-cols-2 gap-8 mb-8">
            <!-- Customer Support -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="bi bi-headset text-3xl text-blue-600 dark:text-blue-400 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Customer Support</h2>
                </div>
                <p class="text-gray-700 dark:text-gray-300 mb-2">
                    For general inquiries and customer support:
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Email: support@example.com
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Phone: +1 (555) 123-4567
                </p>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">
                    Available: Monday - Friday, 9:00 AM - 6:00 PM
                </p>
            </div>

            <!-- Technical Support -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="bi bi-tools text-3xl text-green-600 dark:text-green-400 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Technical Support</h2>
                </div>
                <p class="text-gray-700 dark:text-gray-300 mb-2">
                    For technical issues and emergencies:
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Email: tech@example.com
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Phone: +1 (555) 987-6543
                </p>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">
                    Available: 24/7 for emergencies
                </p>
            </div>

            <!-- Sales Inquiries -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="bi bi-briefcase text-3xl text-purple-600 dark:text-purple-400 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Sales Inquiries</h2>
                </div>
                <p class="text-gray-700 dark:text-gray-300 mb-2">
                    For new subscriptions and upgrades:
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Email: sales@example.com
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Phone: +1 (555) 456-7890
                </p>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">
                    Available: Monday - Saturday, 8:00 AM - 8:00 PM
                </p>
            </div>

            <!-- Billing Support -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <i class="bi bi-credit-card text-3xl text-orange-600 dark:text-orange-400 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Billing Support</h2>
                </div>
                <p class="text-gray-700 dark:text-gray-300 mb-2">
                    For billing and payment questions:
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Email: billing@example.com
                </p>
                <p class="text-gray-900 dark:text-white font-medium">
                    Phone: +1 (555) 321-0987
                </p>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">
                    Available: Monday - Friday, 9:00 AM - 5:00 PM
                </p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="bg-blue-50 dark:bg-blue-900 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Links</h2>
            <ul class="space-y-2">
                @auth
                    <li>
                        <a href="{{ route('panel.customer.dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="bi bi-speedometer2 mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('panel.customer.tickets') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="bi bi-ticket-perforated mr-2"></i>Submit a Support Ticket
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            <i class="bi bi-box-arrow-in-right mr-2"></i>Login to Your Account
                        </a>
                    </li>
                @endauth
                <li>
                    <a href="{{ route('privacy-policy') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        <i class="bi bi-shield-lock mr-2"></i>Privacy Policy
                    </a>
                </li>
                <li>
                    <a href="{{ route('terms-of-service') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                        <i class="bi bi-file-text mr-2"></i>Terms of Service
                    </a>
                </li>
            </ul>
        </div>

        <!-- Office Address -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <i class="bi bi-geo-alt text-3xl text-red-600 dark:text-red-400 mr-3"></i>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Office Address</h2>
            </div>
            <p class="text-gray-700 dark:text-gray-300">
                {{ config('app.name', 'ISP Solution') }}<br>
                123 Main Street<br>
                Suite 100<br>
                City, State 12345<br>
                Country
            </p>
        </div>
    </div>
</div>
@endsection
