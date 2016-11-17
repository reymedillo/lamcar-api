<?php

namespace App\Http\Controllers\Stub;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;

class UserController extends Controller
{

    /**
     * Create a User
     * @param $request
     * @desc Create New User
     */
    protected function postCreate(Request $request)
    {
        $time = new Carbon(Carbon::now());
        return response()->json(
            array(
                'access_token' => str_random(75),
                'expired_date' => $time->addHour(1)->format('Y-m-d H:i:s'),
                'refresh_token' => str_random(75),
                'user_id' => '1',
            ), 200 );
    }

    protected function postOrder($user_id, Request $request)
    {
       return response()->json(
           array(
                'order_id' => '12345',
           ), 200 );
    }

    protected function getUserOrderList($user_id, Request $request)
    {
        return response()->json(
            array(
                'orders' => array(
                    array(
                        'id' => 1,
                        'name' => 'TARO YAMADA',
                        'pickup_location' => 'Marriott Hotel',
                        'pickup_latitude' => '13.476846',
                        'pickup_longitude' => '144.757327',
                        'pickup_location_detail' => 'HOTEL LOBBY',
                        'dropoff_location' => 'DFS Galleria',
                        'dropoff_latitude' => '13.514213',
                        'dropoff_longitude' => '144.806242',
                        'distance' => '2',
                        'pickup_scheduled_date' => '2016-02-15 15:00:00',
                        'fare' => '45.00',
                        'car_number' => '#123-456',
                        'status' => '5',
                    ),
                    array(
                        'id' => 2,
                        'name' => 'TARO YAMADA',
                        'pickup_location' => 'DFS Galleria',
                        'pickup_latitude' => '13.514213',
                        'pickup_longitude' => '144.806242',
                        'pickup_location_detail' => 'In front of entrance',
                        'dropoff_location' => 'Marriott Hotel',
                        'dropoff_latitude' => '13.476846',
                        'dropoff_longitude' => '144.757327',
                        'distance' => '2',
                        'pickup_scheduled_date' => '2016-02-15 18:00:00',
                        'fare' => '45.00',
                        'car_number' => '#123-456',
                        'status' => '1',
                    )
                )
            ), 200 );
    }
 
    protected function getOrder($user_id, $order_id)
    {
        return response()->json(
            array(
                'order' => array(
                    'id' => 1,
                    'name' => 'TARO YAMADA',
                    'pickup_location' => 'Marriott Hotel',
                    'pickup_latitude' => '13.476846',
                    'pickup_longitude' => '144.757327',
                    'pickup_location_detail' => 'HOTEL LOBBY',
                    'dropoff_location' => 'DFS Galleria',
                    'dropoff_latitude' => '13.514213',
                    'dropoff_longitude' => '144.806242',
                    'distance' => '2',
                    'pickup_scheduled_date' => '2016-02-15 15:00:00',
                    'fare' => '45.00',
                    'car_number' => '#123-456',
                    'status' => '1',
                ),
            ), 200 );
    }
    
    protected function postOrderSettlement($user_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }

    protected function postOrderCancel($user_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }

    protected function postOrderPickup($user_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }
 
    protected function postPush($user_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }
 
}
