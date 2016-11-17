<?php

namespace App\Http\Controllers\Stub;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;

class CarController extends Controller
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
                'refresh_token_expired_date' => $time->addDay(1)->format('Y-m-d H:i:s'),
                'car_id' => '1',
            ), 200 );
    }

    protected function getInreceive($car_id)
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
                    'status' => '3',
                ),
            ), 200 );
    }
 
    protected function getOrder($car_id, $order_id)
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
    
    protected function postOrderAccept($car_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }

    protected function postOrderArrive($car_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }

    protected function postOrderPickup($car_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }

    protected function postOrderDropoff($car_id, $order_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }
 
    protected function postPush($car_id)
    {
        return response()->json(
            array(
                'result' => 'success'
            ), 200 );
    }

}
