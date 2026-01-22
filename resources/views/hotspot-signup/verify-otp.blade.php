<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Step 2</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Progress Indicator -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-green-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-green-600 text-white font-bold">✓</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-green-600">Registration</div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-green-600"></div>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-blue-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-blue-600 text-white font-bold">2</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-blue-600">Verify OTP</div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300"></div>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-gray-400 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-gray-200 text-gray-600 font-bold">3</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-gray-500">Profile</div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300"></div>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-gray-400 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-gray-200 text-gray-600 font-bold">4</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-gray-500">Payment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-white text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold mb-2">Verify Your Number</h1>
                    <p class="text-blue-100">We sent a code to {{ $mobile_number }}</p>
                </div>

                <div class="p-6 md:p-8">
                    @if (session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <ul class="text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('hotspot.signup.verify-otp.post') }}" method="POST" class="space-y-6">
                        @csrf

                        <input type="hidden" name="mobile_number" value="{{ $mobile_number }}">

                        <!-- OTP Input -->
                        <div>
                            <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                                Enter 6-digit OTP
                            </label>
                            <input 
                                type="text" 
                                id="otp_code" 
                                name="otp_code" 
                                maxlength="6"
                                pattern="[0-9]{6}"
                                required
                                autocomplete="off"
                                class="w-full text-center text-3xl font-bold tracking-widest px-4 py-4 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                placeholder="000000"
                            >
                        </div>

                        <!-- Timer -->
                        <div class="text-center">
                            <p class="text-sm text-gray-600" id="timer-text">
                                Code expires in <span id="timer" class="font-bold text-blue-600">5:00</span>
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 shadow-lg"
                        >
                            Verify OTP
                        </button>
                    </form>

                    <!-- Resend OTP -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 mb-2">Didn't receive the code?</p>
                        <button 
                            id="resend-btn"
                            onclick="resendOtp()"
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm disabled:text-gray-400 disabled:cursor-not-allowed"
                        >
                            Resend OTP
                        </button>
                        <p id="resend-message" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <a href="{{ route('hotspot.signup') }}" class="text-gray-600 hover:text-gray-800 text-sm">
                    ← Back to registration
                </a>
            </div>
        </div>
    </div>

    <script>
        // OTP Timer
        let expiresAt = {{ $expires_at }};
        let timerInterval;

        function updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = expiresAt - now;

            if (remaining <= 0) {
                document.getElementById('timer-text').innerHTML = '<span class="text-red-600 font-bold">OTP Expired! Please resend.</span>';
                clearInterval(timerInterval);
                return;
            }

            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            document.getElementById('timer').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);

        // Auto-focus OTP input
        document.getElementById('otp_code').focus();

        // Resend OTP
        async function resendOtp() {
            const btn = document.getElementById('resend-btn');
            const message = document.getElementById('resend-message');
            
            btn.disabled = true;
            message.textContent = 'Sending...';
            message.className = 'text-xs text-blue-600 mt-1';

            try {
                const response = await fetch('{{ route("hotspot.signup.resend-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({})
                });

                const data = await response.json();

                if (data.success) {
                    message.textContent = 'OTP sent successfully!';
                    message.className = 'text-xs text-green-600 mt-1';
                    
                    // Update expiry time
                    expiresAt = data.expires_at;
                    
                    // Re-enable button after 60 seconds
                    setTimeout(() => {
                        btn.disabled = false;
                        message.textContent = '';
                    }, 60000);
                } else {
                    message.textContent = data.message || 'Failed to send OTP';
                    message.className = 'text-xs text-red-600 mt-1';
                    btn.disabled = false;
                }
            } catch (error) {
                message.textContent = 'Network error. Please try again.';
                message.className = 'text-xs text-red-600 mt-1';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
