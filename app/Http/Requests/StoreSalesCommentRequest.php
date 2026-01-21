<?php

namespace App\Http\Requests;

use App\Models\SalesComment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesCommentRequest extends FormRequest
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
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', Rule::in(SalesComment::getTypes())],
            'subject' => ['required', 'string', 'max:255'],
            'comment' => ['required', 'string'],
            'contact_date' => ['required', 'date'],
            'next_action' => ['nullable', 'string'],
            'next_action_date' => ['nullable', 'date', 'after_or_equal:today'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10MB max
            'is_private' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Comment type is required.',
            'type.in' => 'Invalid comment type selected.',
            'subject.required' => 'Subject is required.',
            'comment.required' => 'Comment text is required.',
            'contact_date.required' => 'Contact date is required.',
            'next_action_date.after_or_equal' => 'Next action date must be today or later.',
            'attachment.max' => 'Attachment size must not exceed 10MB.',
        ];
    }
}
