<?php

return array(

    'ios_car' => array(
        'environment' => env('APNS_ENVIRONMENT'),
        'certificate' => base_path(env('APNS_CAR_CERTIFICATE')),
        'passPhrase'  => 'password',
        'service'     => 'apns'
    ),
    'ios_user' => array(
        'environment' => env('APNS_ENVIRONMENT'),
        'certificate' => base_path(env('APNS_USER_CERTIFICATE')),
        'passPhrase'  => 'password',
        'service'     => 'apns'
    ),
    'android' => array(
        'environment' => 'production',
        'apiKey'      =>  env('GCM_KEY'),
        'service'     => 'gcm'
    )

);
