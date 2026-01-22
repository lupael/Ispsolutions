<?php

declare(strict_types=1);

namespace App\Http\Requests\HotspotSelfSignup;

use Illuminate\Foundation\Http\FormRequest;

class HotspotPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public access
    }

    public function rules(): array
    {
        return [
            'payment_gateway' => [
                'required',
                'string',
                'in:bkash,nagad,sslcommerz,stripe',
            ],
            'mobile_number' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_gateway.required' => 'Please select a payment method',
            'payment_gateway.in' => 'Invalid payment gateway selected',
            'mobile_number.required' => 'Mobile number is required',
        ];
    }
}
