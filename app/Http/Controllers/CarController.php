<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Order;
use App\Car;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Validator;
use DB;
use App\Helpers;
use App\AuthorizeNet;

class CarController extends Controller
{

    protected $car;

    public function __construct(Car $car, AuthorizeNet $authorize)
    {
        $this->car = $car;
        $this->authorize = $authorize;
    }

    /**
     * Create a Car
     * @param $request
     * @desc Create New Car
     */
    protected function postCreate(Request $request)
    {
        //Check if already exist
        $validator = $this->car->validateCreate($request);
        if($validator->fails()){
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'register']),
                'errors' => $validator->errors()
            ], 422);
        }

        //Register New Car
        $result = $this->car->register($request);

        if($result > 0){
            return response()->json([
                'car_id' => $result
            ], 200);
        }else{
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        }
    }

    /**
     * Update a Car
     * @param $request
     * @desc Update Car
     */
    protected function putUpdate(Request $request, $id)
    {
        //Check if already exist
        $validator = $this->car->validateUpdate($request,$id);
        if($validator->fails()) {
            return response()->json([
                'message'=> trans('custom.fail_to',['name'=>'register']),
                'errors'=>$validator->errors()
            ], 200);
        }

        $request['id'] = $id;

        //Update Car
        $result = $this->car->edit($request);

        if($result > 0){
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }else{
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        }
    }

    protected function getIndex(Request $request)
    {
        $params = array();

        if($request->input('offset'))
            $params['perPage'] = $request->input('offset');

        if($request->input('search'))
            $params['search'] = $request->input('search');

        $cars = Car::getCars($params);

        if(count($cars) <= 0){
            return response()->json([
                'cars'=>[],
                'pagination'=> []
            ], 200);
        }

        return response()->json([
            'cars'=> $cars['cars'],
            'pagination'=> $cars['pagination']
        ], 200);
    }

    protected function getShow($car_id)
    {
        $car = Car::where('cars.id', $car_id)
                ->leftJoin('drivers', 'drivers.id', '=', 'cars.driver_id')
                ->first([
                'cars.id as id',
                'car_type_id',
                'driver_id',
                'drivers.name as driver_name',
                'number as number',
                'note as note'
                ]);

        if(!$car) {
            return response()->json('', 204);
        }
        return response()->json([
            'car' => $car
        ], 200);
    }

    protected function getInreceive($car_id)
    {
        $refines['car_id'] = $car_id;
        $refines['status'] = ['<', config('define.status.dropoff')];
        $order = Order::setFilter($refines)->first();

        if(!$order) {
            return response()->json('', 204);
        }

        return response()->json([
            'order' => $order
        ], 200);
    }

    protected function getOrder($car_id, $order_id)
    {
        $refines['id'] = $order_id;
        $order = Order::setFilter($refines,'car')->first();

        if(!$order) {
            return response()->json('', 204);
        }

        return response()->json([
            'order' => $order
        ], 200);
    }

    protected function postOrderAccept($car_id, $order_id,Request $request)
    {
        $validator = Validator::make($request->all(),[
            'need_time' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'accept']),
                'errors'  => $validator->errors()
            ], 400);
        }

        $date        = date('Y-m-d H:i:s');
        $daynext     = Carbon::parse($date);
        $daynext->addMinutes($request->input('need_time'));
        $arrive_date = $daynext->format('Y-m-d H:i:s');

        $car = Car::findOrFail($car_id);
        $refines['id'] = $order_id;
        $order = Order::setFilter($refines)->firstOrFail();

        if($order->status == config('define.status.payment_completion')) {
            $order->car_id = $car->id;
            $order->driver_id = $car->driver_id;
            $order->accept_date = Carbon::now();
            $order->pickup_scheduled_date = $arrive_date;
            $order->status = config('define.status.accept');
            $order->save();

            exec("php ".base_path("artisan")." push:accept ".$order_id." > /dev/null 2>&1 &");

            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        } else {
            return response()->json([
                'result' => config('define.result.already')
            ], 200);
        }
    }

    protected function postOrderArrive($car_id, $order_id)
    {
        $refines['id'] = $order_id;
        $refines['car_id'] = $car_id;
        $refines['status'] = ["whereIn" => [config('define.status.accept'),config('define.status.arrived')]];
        $order = Order::setFilter($refines)->firstOrFail();

        $order->status = config('define.status.arrived');
        $order->arrive_date = Carbon::now();
        $order->save();

        exec("php ".base_path("artisan")." push:arrive ".$order_id." > /dev/null 2>&1 &");

        return response()->json([
            'result' => config('define.result.success')
        ], 200);
    }

    protected function postOrderPickup($car_id, $order_id)
    {
        $refines['id'] = $order_id;
        $refines['car_id'] = $car_id;
        $refines['status'] = ["whereIn" => [config('define.status.accept'),config('define.status.arrived')]];
        $order = Order::setFilter($refines)->firstOrFail();

        $order->status = config('define.status.pickup');
        $order->pickup_date = Carbon::now();
        $order->save();

        return response()->json([
            'result' => config('define.result.success')
        ], 200);
    }


    protected function postOrderDropoff($car_id, $order_id)
    {
        \DB::beginTransaction();

        $pay_info  = \App\Payment::where('order_id',$order_id)
            ->where('type','authorize')
            ->firstOrFail();

        $refines['id'] = $order_id;
        $refines['car_id'] = $car_id;
        $refines['status'] = config('define.status.pickup');
        $order   = Order::setFilter($refines)->firstOrFail();
        $payment = \App\Payment::createCapture($order_id, config('define.status.dropoff'));

        // Call CaptureAuth API
        $api_response = $this->authorize->captureAuth(
            $pay_info->transaction_id,
            $pay_info->amount,
            $payment->id
        );

        if ($api_response === false || $api_response->isError()) {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        }

        $payment->payment_date = Carbon::now();
        $payment->transaction_response_code = $api_response->transactionResponse->responseCode;
        $payment->transaction_id = $api_response->transactionResponse->transactionId;
        $payment->api_response = json_encode( (array)$api_response );

        $order->status = config('define.status.dropoff');
        $order->dropoff_date = Carbon::now();

        if(!$order->save() || !$payment->save()) {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        }else{
            \DB::commit();
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }
    }

    protected function deleteDestroy($car_id)
    {
        $car = Car::findOrFail($car_id);
        $car->valid = config('define.valid.false');
        $car->updated_at = \Carbon\Carbon::now();

        if(!$car->save()) {
            return response()->json([
                'message'=>trans('custom.error_occured')
            ], 400);
        }else{
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }
    }

    protected function getTypes(Request $request)
    {
        $types = \App\CarType::all(['id', 'name_'.\App::getLocale().' as car_type_name']);
        return response()->json([
            'car_types'=> $types
        ],200);
    }

    public function getCarsAvailable(Request $request)
    {
        $res = \App\BusinessHour::isOpen();
        if(!$res->result){
            return response()->json([
                'cars' => [],
                'message' => $res->message
            ], 200);
        }
        $validator = \Validator::make($request->all(),[
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.error_occured'),
                'errors' => $validator->errors()
            ], 422);
        }

        $available = \App\Car::getAvailableCars(
            $request->input('pickup_latitude'),
            $request->input('pickup_longitude')
        ); 

        if(count($available)) {
            return response()->json([
                'cars'=> $available
            ], 200);
        }

        return response()->json([
            'cars' => $available,
            'message' => trans('custom.no_car_available')
        ], 200);
    }

    protected function postSetLocation($id, Request $request)
    {
        $car = \App\Car::findOrFail($id);
        $validator = \Validator::make($request->all(),[
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.error_occured'),
                'errors' => $validator->errors()
            ], 422);
        }
        \DB::beginTransaction();
        $car->latitude = $request->input('latitude');
        $car->longitude = $request->input('longitude');
        $car->location_update_date = \Carbon\Carbon::now();

        if(!$car->save()) {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        } else {
            \DB::commit();
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }
    }

    protected function postSetDevice($id, Request $request)
    {
        $car = \App\Car::findOrFail($id);
        $validator = \Validator::make($request->all(),[
            'device_id' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.error_occured'),
                'errors' => $validator->errors()
            ], 422);
        }
        \DB::beginTransaction();
        $car->device_id = $request->input('device_id');

        if(!$car->save()) {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        } else {
            \DB::commit();
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }

    }

}
