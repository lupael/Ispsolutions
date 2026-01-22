<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'admin', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'package_id' => ['nullable', 'exists:service_packages,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'billing_period_start' => ['nullable', 'date'],
            'billing_period_end' => ['nullable', 'date', 'after_or_equal:billing_period_start'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,paid,overdue,cancelled,partial'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Customer is required.',
            'user_id.exists' => 'The selected customer is invalid.',
            'amount.required' => 'Invoice amount is required.',
            'amount.min' => 'Invoice amount cannot be negative.',
            'total_amount.required' => 'Total amount is required.',
            'billing_period_end.after_or_equal' => 'Billing period end date must be after or equal to start date.',
            'status.required' => 'Invoice status is required.',
            'status.in' => 'Invalid invoice status selected.',
        ];
    }
}
