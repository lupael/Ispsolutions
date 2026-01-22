<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'category' => 'required|in:technical,billing,general,complaint',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string|min:10|max:2000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'Please enter a ticket subject.',
            'category.required' => 'Please select a category.',
            'priority.required' => 'Please select a priority level.',
            'description.required' => 'Please describe your issue.',
            'description.min' => 'Description must be at least 10 characters.',
            'attachment.max' => 'File size must not exceed 5MB.',
            'attachment.mimes' => 'Only jpg, jpeg, png, pdf, doc, and docx files are allowed.',
        ];
    }
}
