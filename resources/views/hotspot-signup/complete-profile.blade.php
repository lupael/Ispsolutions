<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile - Step 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <div class="flex items-center text-green-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-green-600 text-white font-bold">✓</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-green-600">Verify OTP</div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-green-600"></div>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-blue-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-blue-600 text-white font-bold">3</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-blue-600">Profile</div>
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
                    <h1 class="text-3xl font-bold mb-2">Complete Your Profile</h1>
                    <p class="text-blue-100">Just a few more details...</p>
                </div>

                <div class="p-6 md:p-8">
                    @if (session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <ul class="text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Package Info -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Selected Package</h3>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-bold text-lg text-blue-600">{{ $package->name }}</p>
                                <p class="text-sm text-gray-600">{{ $package->bandwidth_download }}Mbps • {{ $package->validity_days }} days</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-gray-900">৳{{ $package->price }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('hotspot.signup.complete.post') }}" method="POST" class="space-y-6">
                        @csrf

                        <input type="hidden" name="mobile_number" value="{{ $mobile_number }}">

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}"
                                placeholder="Enter your full name"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                        </div>

                        <!-- Email (Optional) -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-gray-400">(Optional)</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                placeholder="your.email@example.com"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                            <p class="mt-2 text-sm text-gray-500">We'll send account notifications to this email</p>
                        </div>

                        <!-- Address (Optional) -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Address <span class="text-gray-400">(Optional)</span>
                            </label>
                            <textarea 
                                id="address" 
                                name="address" 
                                rows="3"
                                placeholder="Your address"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >{{ old('address') }}</textarea>
                        </div>

                        <!-- Mobile Number Display -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mobile Number
                            </label>
                            <div class="flex items-center px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium text-gray-900">{{ $mobile_number }}</span>
                                <span class="ml-2 text-sm text-green-600">(Verified)</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 shadow-lg"
                        >
                            Continue to Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
