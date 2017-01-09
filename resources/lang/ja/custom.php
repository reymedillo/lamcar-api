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
    'error_occured'                 => 'エラー',
    'fail_to'                       => 'It failed to :name .',
    'expired'                       => 'Enable expired.',
    'error_user_not_found'          => 'User not found',
    'error_car_not_found'           => 'Car not found',
    'wrong_credentials'             => 'Incorrect Credentials',
    'wrong_password'                => 'The number/password is incorrect.',
    'error_generating_access_token' => 'Error while generating access token',
    'auth_error'                    => 'Authentication error.',
    'status' => [
        1 => '配車依頼',
        2 => 'ハイヤー確定' ,
        3 => 'ハイヤー到着',
        4 => '依頼完了',
        8 => 'キャンセル' ,
        9 => '決済失敗'
    ],
    'push_accept'                   => 'ハイヤーが決定しました。',
    'push_request'                  => 'リクエストがありました。',
    'push_arrive'                   => 'ハイヤーが到着しました。',
    'push_cancel'                   => 'キャンセルされました。',
    'push_decline'                  => 'ハイヤーが見つからなかったので払い戻し致しました。',
    'card_error'                    => 'カード情報が正しくありません。',
    'closed'                        => '営業時間は:open〜:closeです。',
    'no_car_available'              => '利用可能な車はありません。',
    'authorize' => [
        'api' => [
            'E00003'            => 'カード情報が正しくありません。',
            'E00027'            => '決済に失敗しました。',
        ],
        'tran' => [
            '6'                 => 'クレジットカード番号が不正です。',
            '7'                 => 'クレジットカード有効期限が不正です。',
            '11'                => '重複トランザクションが送信されました。'
        ],
    ],
    'max_people_num'                => '最大:num名',
];
