<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default IP Pool Size
    |--------------------------------------------------------------------------
    |
    | The default number of usable IP addresses in a subnet (excluding
    | network and broadcast addresses).
    |
    */
    'default_pool_size' => env('IPAM_DEFAULT_POOL_SIZE', 254),

    /*
    |--------------------------------------------------------------------------
    | Cleanup Days
    |--------------------------------------------------------------------------
    |
    | Number of days to keep released allocations before permanent cleanup.
    |
    */
    'cleanup_days' => env('IPAM_CLEANUP_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Allocation TTL
    |--------------------------------------------------------------------------
    |
    | Default time-to-live for dynamic IP allocations in seconds.
    |
    */
    'allocation_ttl' => env('IPAM_ALLOCATION_TTL', 86400), // 24 hours

    /*
    |--------------------------------------------------------------------------
    | Allow Subnet Overlap
    |--------------------------------------------------------------------------
    |
    | Whether to allow overlapping subnets. Generally should be false.
    |
    */
    'allow_overlap' => env('IPAM_ALLOW_OVERLAP', false),
];
