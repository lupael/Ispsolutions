<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCableTvSubscriptionRequest extends FormRequest
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
            'user_id' => 'nullable|exists:users,id',
            'package_id' => 'required|exists:cable_tv_packages,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'installation_address' => 'nullable|string|max:500',
            'expiry_date' => 'required|date',
            'status' => 'required|in:active,suspended,expired,cancelled',
            'auto_renew' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'package_id.required' => 'Please select a cable TV package.',
            'package_id.exists' => 'The selected package is invalid.',
            'customer_name.required' => 'Customer name is required.',
            'customer_phone.required' => 'Customer phone number is required.',
            'customer_email.email' => 'Please enter a valid email address.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.date' => 'Please enter a valid expiry date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}
