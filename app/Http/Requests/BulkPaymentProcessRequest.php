<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkPaymentProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'admin', 'manager', 'accountant']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'required|exists:invoices,id',
            'payment_method' => 'required|in:cash,bank_transfer,gateway,card',
            'payment_date' => 'required|date|before_or_equal:+30 days',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_ids.required' => 'Please select at least one invoice.',
            'invoice_ids.array' => 'Invalid invoice selection.',
            'invoice_ids.min' => 'Please select at least one invoice.',
            'invoice_ids.*.exists' => 'One or more selected invoices are invalid.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.before_or_equal' => 'Payment date cannot be more than 30 days in the future.',
        ];
    }
}
