<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store SMS Payment Request
 * 
 * Validates SMS payment purchase requests from operators
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: SMS Payment Integration
 */
class StoreSmsPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only operators, sub-operators, and admins can purchase SMS credits
        return $this->user()->hasAnyRole(['admin', 'operator', 'sub-operator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sms_quantity' => [
                'required',
                'integer',
                'min:100',
                'max:100000',
            ],
            'payment_method' => [
                'required',
                'string',
                'in:bkash,nagad,rocket,sslcommerz',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:1000000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sms_quantity.required' => 'Please specify the number of SMS credits to purchase.',
            'sms_quantity.min' => 'Minimum purchase quantity is 100 SMS credits.',
            'sms_quantity.max' => 'Maximum purchase quantity is 100,000 SMS credits per transaction.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least 1.',
            'amount.max' => 'Payment amount cannot exceed 1,000,000.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'sms_quantity' => 'SMS quantity',
            'payment_method' => 'payment method',
            'amount' => 'payment amount',
        ];
    }
}
