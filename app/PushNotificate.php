<?php
namespace App;
use GuzzleHttp\Client;

class PushNotificate {

    public function __construct() {
        $this->client = new Client;
    }

    public function gcm_push($device_id, $message="", $extras = []) {
        $headers = [
            'Authorization' => 'key=' . config('push-notification.android.apiKey'),
            'Content-Type'  => 'application/json',
        ];

        $messages = [
            'message' => $message,
        ];

        if( is_array($extras) && !empty($extras) ) {
            $messages = array_merge($extras, $messages);
        }

        $body = [
            'registration_ids' => [$device_id],
            'data'             => $messages,
        ];
        $request = $this->client->post( config('push-notification.android.url'), [
            'headers' => $headers,
            'json'    => $body,
            'verify'  => false,
        ]);
        return $request->getBody();
    }

    public function ios_push($device, $message="", $extras=[], $to=null) {
        $messages = [
            'message' => $message,
        ];

        if( is_array($extras) && !empty($extras) ) {
            $messages = array_merge($extras, $messages);
        }
        switch ($to) {
            case 'user':
                $push = \PushNotification::app('ios_user');
                break;
            
            default:
                $push = \PushNotification::app('ios_car');
                break;
        }
        $push->to($device)->send($messages);
    }

}
