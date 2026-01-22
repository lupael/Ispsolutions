<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotspot Signup - Step 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Progress Indicator -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-blue-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-blue-600 text-white font-bold">1</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-blue-600">Registration</div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300"></div>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-gray-400 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-gray-200 text-gray-600 font-bold">2</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-gray-500">Verify OTP</div>
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
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-white">
                    <h1 class="text-3xl font-bold mb-2">Welcome to Hotspot!</h1>
                    <p class="text-blue-100">Sign up for instant internet access</p>
                </div>

                <div class="p-6 md:p-8">
                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('hotspot.signup.request-otp') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Mobile Number -->
                        <div>
                            <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Mobile Number <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="tel" 
                                id="mobile_number" 
                                name="mobile_number" 
                                value="{{ old('mobile_number') }}"
                                placeholder="01712345678"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                            <p class="mt-2 text-sm text-gray-500">We'll send an OTP to this number</p>
                        </div>

                        <!-- Package Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Select Package <span class="text-red-500">*</span>
                            </label>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($packages as $package)
                                    <label class="relative cursor-pointer">
                                        <input 
                                            type="radio" 
                                            name="package_id" 
                                            value="{{ $package->id }}" 
                                            {{ old('package_id') == $package->id ? 'checked' : '' }}
                                            class="peer sr-only"
                                            required
                                        >
                                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition">
                                            <div class="flex justify-between items-start mb-2">
                                                <h3 class="font-bold text-lg text-gray-900">{{ $package->name }}</h3>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold text-blue-600">৳{{ $package->price }}</div>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mb-3">{{ $package->description }}</p>
                                            <div class="space-y-1 text-sm text-gray-700">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>Speed: {{ $package->bandwidth_download }}Mbps</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span>Valid for {{ $package->validity_days }} days</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="flex items-start">
                            <input 
                                type="checkbox" 
                                id="terms" 
                                required
                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="terms" class="ml-2 text-sm text-gray-600">
                                I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> 
                                and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 shadow-lg"
                        >
                            Send OTP
                        </button>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="#" class="text-blue-600 hover:underline font-medium">Login here</a>
                </p>
                <p class="text-gray-500 text-sm mt-2">
                    Need help? Contact support: <a href="tel:+880123456789" class="text-blue-600">+880 123 456 789</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
