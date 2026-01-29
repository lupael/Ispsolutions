<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Auto-Debit Settings Request
 *
 * Validates auto-debit configuration updates
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Auto-Debit System
 */
class UpdateAutoDebitSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'auto_debit_enabled' => ['required', 'boolean'],
            'auto_debit_payment_method' => [
                'required_if:auto_debit_enabled,true',
                'nullable',
                'string',
                'in:bkash,nagad,rocket,ssl_commerce,bank_transfer',
            ],
            'auto_debit_max_retries' => ['nullable', 'integer', 'min:1', 'max:10'],
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
            'auto_debit_enabled.required' => 'Auto-debit status is required',
            'auto_debit_enabled.boolean' => 'Auto-debit status must be true or false',
            'auto_debit_payment_method.required_if' => 'Payment method is required when auto-debit is enabled',
            'auto_debit_payment_method.in' => 'Invalid payment method selected',
            'auto_debit_max_retries.integer' => 'Max retries must be a number',
            'auto_debit_max_retries.min' => 'Max retries must be at least 1',
            'auto_debit_max_retries.max' => 'Max retries cannot exceed 10',
        ];
    }
}
