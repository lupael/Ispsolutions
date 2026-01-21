<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(Lead::getStatuses())],
            'source' => ['nullable', Rule::in(Lead::getSources())],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string'],
            'next_follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Lead name is required.',
            'email.email' => 'Please provide a valid email address.',
            'status.required' => 'Lead status is required.',
            'status.in' => 'Invalid lead status selected.',
            'probability.min' => 'Probability must be at least 0%.',
            'probability.max' => 'Probability cannot exceed 100%.',
            'expected_close_date.after' => 'Expected close date must be a future date.',
        ];
    }
}
