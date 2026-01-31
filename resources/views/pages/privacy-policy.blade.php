@extends('layouts.public')

@section('title', 'Privacy Policy')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Privacy Policy</h1>
    
    <div class="prose dark:prose-invert max-w-none">
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Last updated: {{ date('F d, Y') }}
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">1. Information We Collect</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We collect information that you provide directly to us, including when you create or modify your account, 
            request services, contact customer support, or otherwise communicate with us. This information may include:
        </p>
        <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
            <li>Name and contact information</li>
            <li>Billing and payment information</li>
            <li>Service usage information</li>
            <li>Technical and device information</li>
            <li>Communications and correspondence</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">2. How We Use Your Information</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We use the information we collect to:
        </p>
        <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
            <li>Provide, maintain, and improve our services</li>
            <li>Process transactions and send related information</li>
            <li>Send technical notices, updates, and support messages</li>
            <li>Respond to your comments and questions</li>
            <li>Monitor and analyze trends, usage, and activities</li>
            <li>Detect, prevent, and address technical issues and fraud</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">3. Information Sharing</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We do not share your personal information with third parties except:
        </p>
        <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
            <li>With your consent</li>
            <li>To comply with laws or respond to lawful requests</li>
            <li>To protect the rights, property, or safety of our company and users</li>
            <li>With service providers who perform services on our behalf</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">4. Data Security</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            We take reasonable measures to help protect information about you from loss, theft, misuse, 
            unauthorized access, disclosure, alteration, and destruction.
        </p>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">5. Your Rights</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            You have the right to:
        </p>
        <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300 mb-4">
            <li>Access your personal information</li>
            <li>Correct inaccurate information</li>
            <li>Request deletion of your information</li>
            <li>Object to our use of your information</li>
            <li>Request a copy of your information</li>
        </ul>

        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mt-8 mb-4">6. Contact Us</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            If you have any questions about this Privacy Policy, please contact us at:
            <a href="{{ route('support') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Support</a>
        </p>
    </div>
</div>
@endsection
