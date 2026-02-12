<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commission Rates
    |--------------------------------------------------------------------------
    |
    | This file is for storing the commission rates for different user roles.
    | You can specify the commission rate for each role in percentage.
    |
    */

    'rates' => [
        'operator' => [
            'direct' => 10.0, // 10% for direct sales
        ],
        'sub-operator' => [
            'direct' => 5.0, // 5% for direct sales
            'parent' => 3.0, // 3% for parent operator
        ],
    ],

];
