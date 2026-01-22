<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
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
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|min:1',
            'action' => 'required|string|in:activate,deactivate,suspend,delete,lock,unlock,generate_invoice',
            'confirm' => 'accepted',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Please select at least one item.',
            'ids.array' => 'Invalid selection format.',
            'ids.min' => 'Please select at least one item.',
            'ids.*.integer' => 'Invalid item ID.',
            'action.required' => 'Please select an action to perform.',
            'action.in' => 'Invalid action selected.',
            'confirm.accepted' => 'Please confirm the action.',
        ];
    }
}
