<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('isp') || auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:payment_gateways,slug'],
            'is_active' => ['sometimes', 'boolean'],
            'test_mode' => ['sometimes', 'boolean'],
            'configuration' => ['required', 'array'],
            'configuration.api_key' => ['required_if:slug,stripe,paypal,razorpay', 'string'],
            'configuration.api_secret' => ['required_if:slug,stripe,paypal,razorpay', 'string'],
            'configuration.merchant_id' => ['required_if:slug,bkash,nagad,sslcommerz', 'string'],
            'configuration.merchant_key' => ['required_if:slug,bkash,nagad,sslcommerz', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'configuration.api_key.required_if' => 'API Key is required for this payment gateway.',
            'configuration.merchant_id.required_if' => 'Merchant ID is required for this payment gateway.',
        ];
    }
}
