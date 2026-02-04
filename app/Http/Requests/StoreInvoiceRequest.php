<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('isp') || auth()->user()->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'billing_period_start' => ['nullable', 'date'],
            'billing_period_end' => ['nullable', 'date', 'after_or_equal:billing_period_start'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'billing_period_end.after_or_equal' => 'Billing period end date must be after or equal to start date.',
        ];
    }
}
