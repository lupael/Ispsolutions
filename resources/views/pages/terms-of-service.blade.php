@extends('layouts.public')

@section('title', 'Terms of Service')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Terms of Service</h1>
    
    <div class="prose dark:prose-invert max-w-none">
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Last updated: {{ date('F d, Y') }}
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">1. Acceptance of Terms</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            By accessing and using this service, you accept and agree to be bound by the terms and provisions of this agreement. 
            If you do not agree to abide by these terms, please do not use this service.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">2. Service Description</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We provide internet service provider management solutions including customer management, billing, 
            network monitoring, and related services. The specific services available to you may vary based on your subscription plan.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">3. User Obligations</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            You agree to:
        </p>
        <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
            <li>Provide accurate and complete registration information</li>
            <li>Maintain the security of your account and password</li>
            <li>Notify us immediately of any unauthorized use of your account</li>
            <li>Use the service in compliance with all applicable laws and regulations</li>
            <li>Not use the service for any illegal or unauthorized purpose</li>
            <li>Not interfere with or disrupt the service or servers</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">4. Payment Terms</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Services are billed according to your chosen subscription plan. You agree to:
        </p>
        <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
            <li>Pay all fees and charges associated with your account</li>
            <li>Provide accurate billing information</li>
            <li>Pay invoices within the specified time frame</li>
            <li>Accept responsibility for all charges incurred under your account</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">5. Service Availability</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            While we strive to provide uninterrupted service, we do not guarantee that the service will be available 
            at all times. We may suspend or terminate service for maintenance, upgrades, or for violation of these terms.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">6. Intellectual Property</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            All content, features, and functionality of the service are owned by us and are protected by international 
            copyright, trademark, patent, trade secret, and other intellectual property laws.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">7. Limitation of Liability</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting 
            from your use of or inability to use the service.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">8. Termination</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We reserve the right to terminate or suspend your account and access to the service at our sole discretion, 
            without notice, for conduct that we believe violates these Terms of Service or is harmful to other users, 
            us, or third parties, or for any other reason.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">9. Changes to Terms</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We reserve the right to modify these terms at any time. We will notify users of any material changes. 
            Your continued use of the service after such modifications constitutes your acceptance of the updated terms.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">10. Contact Information</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            If you have any questions about these Terms of Service, please contact us at:
            <a href="{{ route('support') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Support</a>
        </p>
    </div>
</div>
@endsection
