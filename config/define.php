<?php
return 
[
    'status' => [
        'reserve'               => 1,
        'payment_completion'    => 2, 
        'accept'                => 3,
        'arrived'               => 4,
        'pickup'                => 5,
        'dropoff'               => 6,
        'declined'              => 8,
        'cancel'                => 9,
    ],
    'add_api_token' => [
        'days'                  => 1,
        'hours'                 => 0,
        'minutes'               => 0,
    ],
    'add_refresh_token' => [
        'days'                  => 0,
        'hours'                 => 1,
        'minutes'               => 0,
    ],
    'add_payment_token' => [
        'days'                  => 0,
        'hours'                 => 0,
        'minutes'               => 10
    ],
    'valid' => [
        'true'                  => 1,
        'false'                 => 0
    ],
    'result' => [
        'success'               => 'success',
        'failure'               => 'failure',
        'already'               => 'already'
    ],
    'authorize' => [
        'result_code' => [
            'ok'                => 'OK',
            'error'             => 'ERROR'
        ],
        'response_code' => [
            'approved'          => 1,
            'declined'          => 2,
            'error'             => 3,
            'held_for_review'   => 4
        ]
    ],
    'miles_available'           => 3.0,
    'void_add_minutes'          => 10,
    'seconds_available'         => 60,
    'token_extension_time'      => 10
];
