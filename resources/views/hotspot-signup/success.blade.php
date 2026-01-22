<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful!</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
                <!-- Success Header -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-12 text-white text-center">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold mb-3">Success!</h1>
                    <p class="text-xl text-green-100">Your account is now active</p>
                </div>

                <!-- Account Details -->
                <div class="p-8">
                    <div class="mb-8 text-center">
                        <p class="text-gray-600 mb-4">Your hotspot account has been activated successfully!</p>
                        <p class="text-sm text-gray-500">We've sent your login credentials via SMS</p>
                    </div>

                    <!-- Credentials Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6 mb-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Your Login Credentials</h2>
                        
                        <div class="space-y-4">
                            <div class="bg-white rounded-lg p-4">
                                <label class="text-xs font-medium text-gray-500 uppercase">Username</label>
                                <div class="flex items-center justify-between mt-1">
                                    <p class="text-2xl font-bold text-gray-900 font-mono">{{ $user->username }}</p>
                                    <button 
                                        onclick="copyToClipboard('{{ $user->username }}', 'username')" 
                                        class="text-blue-600 hover:text-blue-800 p-2"
                                        title="Copy username"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if(isset($user->plain_password))
                                <div class="bg-white rounded-lg p-4">
                                    <label class="text-xs font-medium text-gray-500 uppercase">Password</label>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-2xl font-bold text-gray-900 font-mono">{{ $user->plain_password }}</p>
                                        <button 
                                            onclick="copyToClipboard('{{ $user->plain_password }}', 'password')" 
                                            class="text-blue-600 hover:text-blue-800 p-2"
                                            title="Copy password"
                                        >
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="bg-white rounded-lg p-4">
                                <label class="text-xs font-medium text-gray-500 uppercase">Valid Until</label>
                                <p class="text-xl font-bold text-gray-900 mt-1">
                                    {{ $user->expires_at->format('d M Y, h:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 font-medium">Important: Save your credentials!</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    These credentials have been sent to your mobile number via SMS. 
                                    Please save them securely as you'll need them to access the hotspot.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-8">
                        <h3 class="font-bold text-lg text-gray-900 mb-4">Next Steps</h3>
                        <ol class="space-y-3">
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-3">1</span>
                                <div>
                                    <p class="font-medium text-gray-900">Connect to WiFi</p>
                                    <p class="text-sm text-gray-600">Connect to the hotspot WiFi network</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-3">2</span>
                                <div>
                                    <p class="font-medium text-gray-900">Open Browser</p>
                                    <p class="text-sm text-gray-600">Open any website in your browser</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-3">3</span>
                                <div>
                                    <p class="font-medium text-gray-900">Login</p>
                                    <p class="text-sm text-gray-600">Enter your username and password</p>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold mr-3">4</span>
                                <div>
                                    <p class="font-medium text-gray-900">Enjoy!</p>
                                    <p class="text-sm text-gray-600">Start browsing the internet</p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <a 
                            href="#" 
                            class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 shadow-lg text-center"
                        >
                            Login Now
                        </a>
                        <a 
                            href="{{ route('hotspot.signup') }}" 
                            class="bg-white border-2 border-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg hover:bg-gray-50 transition duration-200 text-center"
                        >
                            New Registration
                        </a>
                    </div>

                    <!-- Support -->
                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-600 mb-2">Need assistance?</p>
                        <a href="tel:+880123456789" class="text-blue-600 hover:text-blue-800 font-medium">
                            📞 Contact Support: +880 123 456 789
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text, type) {
            navigator.clipboard.writeText(text).then(() => {
                // Show success message
                const message = type === 'username' ? 'Username' : 'Password';
                alert(`${message} copied to clipboard!`);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy. Please copy manually.');
            });
        }
    </script>
</body>
</html>
