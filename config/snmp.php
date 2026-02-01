<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SNMP Trap Allowed IPs
    |--------------------------------------------------------------------------
    |
    | List of IP addresses or CIDR ranges that are allowed to send SNMP traps
    | to the trap receiver endpoints. This prevents unauthorized devices from
    | sending fake traps and manipulating OLT health status.
    |
    | Examples:
    | - Single IP: '192.168.1.100'
    | - CIDR range: '192.168.1.0/24'
    | - Multiple: ['192.168.1.100', '10.0.0.0/8']
    |
    | Leave empty to allow all IPs (NOT recommended for production)
    |
    */
    'trap_allowed_ips' => env('SNMP_TRAP_ALLOWED_IPS') 
        ? explode(',', env('SNMP_TRAP_ALLOWED_IPS')) 
        : [],
    
    /*
    |--------------------------------------------------------------------------
    | SNMP Trap Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Maximum number of trap requests allowed per minute per IP address.
    | This helps prevent abuse even from allowed IPs.
    |
    */
    'trap_rate_limit' => env('SNMP_TRAP_RATE_LIMIT', 60),
];
