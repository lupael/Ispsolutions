<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'isp', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:customers,id',
            'action' => 'required|in:activate,deactivate,extend_validity,change_package',
            'package_id' => 'required_if:action,change_package|exists:packages,id',
            'extend_days' => 'required_if:action,extend_validity|integer|min:1|max:365',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Invalid user selection.',
            'user_ids.min' => 'Please select at least one user.',
            'user_ids.*.exists' => 'One or more selected users are invalid.',
            'action.required' => 'Please select an action.',
            'action.in' => 'Invalid action selected.',
            'package_id.required_if' => 'Package selection is required for package change action.',
            'extend_days.required_if' => 'Extension days are required for extend validity action.',
            'extend_days.min' => 'Extension must be at least 1 day.',
            'extend_days.max' => 'Extension cannot exceed 365 days.',
        ];
    }
}
