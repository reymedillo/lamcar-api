<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Language Lines
    |--------------------------------------------------------------------------
    | custom API languaege texts.
    |
    |
    |
    |
    */
    'error_occured'                 => 'An error occurred.',
    'fail_to'                       => 'It failed to :name .',
    'expired'                       => 'Enable expired.',
    'error_user_not_found'          => 'User not found',
    'error_car_not_found'           => 'Car not found',
    'wrong_credentials'             => 'Incorrect Credentials',
    'wrong_password'                => 'Password is incorrect.',
    'wrong_number'                  => 'Number does not exist',
    'error_generating_access_token' => 'Error while generating access token',
    'auth_error'                    => 'Authentication error.',
    'status' => [
        1  => 'reserve',
        2  => 'accept',
        3  => 'arrived',
        4  => 'dropoff',
        8  => 'cancel',
        9  => 'settlement_failure'
    ],
    'push_accept'                   => 'The hire was accepted',
    'push_request'                  => 'There was a request.',
    'push_arrive'                   => 'The hire was arrived.',
    'push_cancel'                   => 'This order was cancelled.',
    'push_decline'                  => 'We refund because the hire could not be found.',
    'card_error'                    => 'The card information is incorrect.',
    'closed'                        => 'Open time is from :open to :close.',
    'no_car_available'              => 'Does not have available cars.',
    'authorize' => [
        'api' => [
            'E00003'            => 'The card information is incorrect.',
            'E00027'            => 'The transaction was unsuccessful.'
        ],
        'tran' => [
            '6'                 => 'The credit card number is invalid.',
            '7'                 => 'Credit card expiration date is invalid.',
            '11'                => 'A duplicate transaction has been submitted.'
        ],
    ],
    'max_people_num'                => 'max :num people',
];
