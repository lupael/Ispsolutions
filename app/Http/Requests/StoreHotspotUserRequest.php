<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotspotUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['super-admin', 'admin', 'manager', 'staff', 'operator', 'sub-operator']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:hotspot_users,username',
            'password' => 'required|string|min:4|max:255',
            'package_id' => 'required|exists:packages,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'mac_address' => 'nullable|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'mikrotik_router_id' => 'required|exists:mikrotik_routers,id',
            'validity_days' => 'required|integer|min:1|max:365',
            'data_limit_mb' => 'nullable|numeric|min:0',
            'start_time' => 'nullable|date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 4 characters.',
            'package_id.required' => 'Please select a package.',
            'package_id.exists' => 'The selected package is invalid.',
            'mikrotik_router_id.required' => 'Please select a MikroTik router.',
            'mikrotik_router_id.exists' => 'The selected router is invalid.',
            'validity_days.required' => 'Validity days are required.',
            'validity_days.min' => 'Validity must be at least 1 day.',
            'validity_days.max' => 'Validity cannot exceed 365 days.',
            'mac_address.regex' => 'Please enter a valid MAC address.',
        ];
    }
}
