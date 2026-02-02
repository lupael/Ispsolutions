<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OltRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization as needed (e.g. policies/permissions)
        return $this->user()?->can('manage', \App\Models\Olt::class) ?? true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'ip_address' => ['required', 'ip'],
            'brand' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:100'],
            'management_protocol' => ['required', 'in:ssh,telnet,snmp'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'snmp_port' => ['nullable', 'integer', 'between:1,65535'],
            'snmp_community' => ['nullable', 'string', 'max:500'],
            'username' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'onu_type' => ['nullable', 'in:epon,gpon,xpon'], // support ONU type validation
            'total_ports' => ['nullable','integer','min:0'],
            'max_onus' => ['nullable','integer','min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $protocol = $this->input('management_protocol');

            if (in_array($protocol, ['ssh', 'telnet']) && ! $this->filled('port')) {
                $validator->errors()->add('port', 'Port is required for SSH/Telnet management protocol.');
            }

            if ($protocol === 'snmp' && ! $this->filled('snmp_port')) {
                $validator->errors()->add('snmp_port', 'SNMP port is required for SNMP management protocol.');
            }

            // If SSH/Telnet selected ensure username/password present
            if (in_array($protocol, ['ssh', 'telnet']) && (! $this->filled('username') || ! $this->filled('password'))) {
                $validator->errors()->add('credentials', 'Username and password are required for SSH/Telnet devices.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'ip_address.ip' => 'Please provide a valid IPv4 or IPv6 address.',
            'management_protocol.in' => 'Management protocol must be one of: ssh, telnet, snmp.',
            'onu_type.in' => 'ONU type must be one of: epon, gpon, xpon.',
        ];
    }
}