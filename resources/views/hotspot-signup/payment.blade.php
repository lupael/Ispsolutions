<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Step 4</title>
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
                        <div class="flex items-center text-green-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-green-600 text-white font-bold">✓</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-green-600">Profile</div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-green-600"></div>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center">
                        <div class="flex items-center text-blue-600 relative">
                            <div class="rounded-full h-12 w-12 flex items-center justify-center bg-blue-600 text-white font-bold">4</div>
                            <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase text-blue-600">Payment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto">
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Payment Method Selection -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-6 text-white">
                            <h1 class="text-2xl font-bold">Select Payment Method</h1>
                        </div>

                        <div class="p-6">
                            @if ($errors->any())
                                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                                    <ul class="text-sm text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('hotspot.signup.payment.post', ['user' => $user->id]) }}" method="POST" id="payment-form">
                                @csrf
                                <input type="hidden" name="mobile_number" value="{{ $user->phone_number }}">

                                <div class="space-y-4">
                                    @if($paymentGateways->where('slug', 'bkash')->first())
                                        <label class="relative cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="payment_gateway" 
                                                value="bkash" 
                                                class="peer sr-only"
                                                required
                                            >
                                            <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-pink-400 peer-checked:border-pink-500 peer-checked:bg-pink-50 transition flex items-center">
                                                <div class="flex-shrink-0 w-16 h-16 bg-pink-500 rounded-lg flex items-center justify-center text-white font-bold text-xl mr-4">
                                                    bKash
                                                </div>
                                                <div class="flex-grow">
                                                    <h3 class="font-bold text-lg text-gray-900">bKash</h3>
                                                    <p class="text-sm text-gray-600">Pay with bKash mobile wallet</p>
                                                </div>
                                                <svg class="w-6 h-6 text-pink-500 hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </label>
                                    @endif

                                    @if($paymentGateways->where('slug', 'nagad')->first())
                                        <label class="relative cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="payment_gateway" 
                                                value="nagad" 
                                                class="peer sr-only"
                                            >
                                            <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-orange-400 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition flex items-center">
                                                <div class="flex-shrink-0 w-16 h-16 bg-orange-500 rounded-lg flex items-center justify-center text-white font-bold text-xl mr-4">
                                                    Nagad
                                                </div>
                                                <div class="flex-grow">
                                                    <h3 class="font-bold text-lg text-gray-900">Nagad</h3>
                                                    <p class="text-sm text-gray-600">Pay with Nagad mobile wallet</p>
                                                </div>
                                            </div>
                                        </label>
                                    @endif

                                    @if($paymentGateways->where('slug', 'sslcommerz')->first())
                                        <label class="relative cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="payment_gateway" 
                                                value="sslcommerz" 
                                                class="peer sr-only"
                                            >
                                            <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition flex items-center">
                                                <div class="flex-shrink-0 w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-4">
                                                    SSL
                                                </div>
                                                <div class="flex-grow">
                                                    <h3 class="font-bold text-lg text-gray-900">SSLCommerz</h3>
                                                    <p class="text-sm text-gray-600">Credit/Debit Card, Mobile Banking</p>
                                                </div>
                                            </div>
                                        </label>
                                    @endif

                                    @if($paymentGateways->where('slug', 'stripe')->first())
                                        <label class="relative cursor-pointer">
                                            <input 
                                                type="radio" 
                                                name="payment_gateway" 
                                                value="stripe" 
                                                class="peer sr-only"
                                            >
                                            <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-purple-400 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition flex items-center">
                                                <div class="flex-shrink-0 w-16 h-16 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-xl mr-4">
                                                    Stripe
                                                </div>
                                                <div class="flex-grow">
                                                    <h3 class="font-bold text-lg text-gray-900">Stripe</h3>
                                                    <p class="text-sm text-gray-600">International cards accepted</p>
                                                </div>
                                            </div>
                                        </label>
                                    @endif

                                    @if($paymentGateways->isEmpty())
                                        <div class="text-center py-8">
                                            <p class="text-gray-600">No payment gateways available. Please contact support.</p>
                                        </div>
                                    @endif
                                </div>

                                @if($paymentGateways->isNotEmpty())
                                    <button 
                                        type="submit" 
                                        class="w-full mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 px-6 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition duration-200 shadow-lg"
                                    >
                                        Proceed to Payment
                                    </button>
                                @endif
                            </form>

                            <!-- Security Notice -->
                            <div class="mt-6 flex items-start bg-gray-50 p-4 rounded-lg">
                                <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Secure Payment</p>
                                    <p class="text-xs text-gray-600 mt-1">Your payment information is encrypted and secure</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden sticky top-8">
                        <div class="bg-gray-50 px-6 py-4 border-b">
                            <h2 class="font-bold text-lg text-gray-900">Order Summary</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600">Package</p>
                                    <p class="font-bold text-gray-900">{{ $package->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Speed</p>
                                    <p class="font-semibold text-gray-900">{{ $package->bandwidth_download }}Mbps</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Validity</p>
                                    <p class="font-semibold text-gray-900">{{ $package->validity_days }} days</p>
                                </div>
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <p class="text-gray-600">Subtotal</p>
                                        <p class="font-semibold text-gray-900">৳{{ number_format($package->price, 2) }}</p>
                                    </div>
                                    <div class="flex justify-between items-center mb-4">
                                        <p class="text-gray-600">Tax</p>
                                        <p class="font-semibold text-gray-900">৳0.00</p>
                                    </div>
                                    <div class="flex justify-between items-center border-t pt-4">
                                        <p class="text-lg font-bold text-gray-900">Total</p>
                                        <p class="text-2xl font-bold text-blue-600">৳{{ number_format($package->price, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
