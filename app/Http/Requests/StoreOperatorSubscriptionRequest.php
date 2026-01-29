<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Operator Subscription Request
 * 
 * Validates operator subscription creation requests
 * Reference: REFERENCE_SYSTEM_QUICK_GUIDE.md - Phase 2: Subscription Payments
 */
class StoreOperatorSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only operators, sub-operators, and admins can create subscriptions
        return $this->user()->hasAnyRole(['admin', 'operator', 'sub-operator', 'superadmin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subscription_plan_id' => [
                'required',
                'integer',
                'exists:subscription_plans,id',
            ],
            'billing_cycle' => [
                'required',
                'integer',
                'in:1,3,6,12', // 1=monthly, 3=quarterly, 6=semi-annual, 12=yearly
            ],
            'payment_method' => [
                'required',
                'string',
                'in:bkash,nagad,rocket,sslcommerz,bank_transfer',
            ],
            'auto_renew' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subscription_plan_id.required' => 'Please select a subscription plan.',
            'subscription_plan_id.exists' => 'The selected subscription plan does not exist.',
            'billing_cycle.required' => 'Please select a billing cycle.',
            'billing_cycle.in' => 'Invalid billing cycle selected. Must be 1, 3, 6, or 12 months.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'subscription_plan_id' => 'subscription plan',
            'billing_cycle' => 'billing cycle',
            'payment_method' => 'payment method',
            'auto_renew' => 'auto-renew option',
        ];
    }
}
