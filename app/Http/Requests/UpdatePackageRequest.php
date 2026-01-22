<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'bandwidth_up' => 'required|numeric|min:0',
            'bandwidth_down' => 'required|numeric|min:0',
            'bandwidth_unit' => 'required|in:kbps,mbps,gbps',
            'price_monthly' => 'required|numeric|min:0',
            'price_daily' => 'nullable|numeric|min:0',
            'validity_days' => 'required|integer|min:1',
            'validity_unit' => 'required|in:days,months',
            'data_limit' => 'nullable|numeric|min:0',
            'data_limit_unit' => 'nullable|in:mb,gb,tb',
            'connection_type' => 'required|in:pppoe,hotspot,static_ip',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
            'mikrotik_profile' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The package name is required.',
            'bandwidth_up.required' => 'Upload bandwidth is required.',
            'bandwidth_up.min' => 'Upload bandwidth must be greater than 0.',
            'bandwidth_down.required' => 'Download bandwidth is required.',
            'bandwidth_down.min' => 'Download bandwidth must be greater than 0.',
            'price_monthly.required' => 'Monthly price is required.',
            'price_monthly.min' => 'Price cannot be negative.',
            'validity_days.required' => 'Validity period is required.',
            'validity_days.min' => 'Validity must be at least 1 day.',
            'connection_type.required' => 'Please select a connection type.',
            'connection_type.in' => 'Invalid connection type selected.',
        ];
    }
}
