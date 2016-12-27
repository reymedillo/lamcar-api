<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\User;
use App\Car;
use App\Order;
use App\AccessToken;
use App\Fare;
use App\Payment;
use App\BusinessHour;
use Carbon\Carbon;
use Validator;
use App\Http\Controllers\Auth\UserAuthController;
use App\AuthorizeNet;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user, AuthorizeNet $authorize, Car $car)
    {
        $this->user         = $user;
        $this->authorize    = $authorize;
        $this->car          = $car;
    }

    public function get($userId)
    {
        $user = User::find($userId);
        return response()->json($user, 200);
    }

    /**
     * Create a User
     * @param $request
     * @desc Create New User
     */
    protected function postCreate(Request $request)
    {
        $validator = $this->user->validateCreate($request);
        if($validator->fails()){
            return response()->json([
                'message' => trans('custom.error_occured'),
                'errors'  => $validator->errors()
            ] , 422 );
        }

        $user = $this->user->checkUserInput($request);

        if(is_null($user)){
            if($this->user->register($request)){
                $user = $this->user;
            }else{
                return response()->json([
                   'message' => trans('custom.error_occured'),
                ], 400);
            }
        }

        $request['account_id'] = $user->id;
        $request['role'] = 'user';

        $login_token = AccessToken::createTokens($request);  //save to db
        if(!$login_token){
            return response()->json([
                'message' => trans('custom.error_occured'),
            ], 400);
        }else{
            return response()->json([// response from db
                'access_token'  => $login_token['api_token'],
                'expired_date'  => $login_token['expired_date'],
                'refresh_token' => $login_token['refresh_token'],
                'user_id'       => $user->id,
            ], 200);
        }
    }

    /**
     * UserRegistOrderAPI
     * @param $request
     * @desc | Create new order from the registered user
     */
    protected function postOrder($user_id, Request $request)
    {
        $res = \App\BusinessHour::isOpen();
        if(!$res->result){
            return response()->json([
                'message' => $res->message
            ], 412);
        }

        $validator = Order::validateCreate($request);
        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.error_occured'),
                'errors'  => $validator->errors()
            ], 422);
        }
       
        $cars = $this->car->getAvailableCars(
            $request->input('pickup_latitude'),
            $request->input('pickup_longitude'),
            $request->input('car_type_id')
        );    

        if (count($cars) == 0) {
            return response()->json([
                'message' => trans('custom.no_car_available')
            ], 412);
        }

        $order = Order::create([
            'user_id'                   => $user_id,
            'name'                      => $request->input('name'),
            'contact'                   => $request->input('contact'),
            'pickup_location'           => $request->input('pickup_location'),
            'pickup_latitude'           => $request->input('pickup_latitude'),
            'pickup_longitude'          => $request->input('pickup_longitude'),
            'pickup_location_detail'    => $request->input('pickup_location_detail'),
            'dropoff_location'          => $request->input('dropoff_location'),
            'dropoff_latitude'          => $request->input('dropoff_latitude'),
            'dropoff_longitude'         => $request->input('dropoff_longitude'),
            'distance'                  => $request->input('distance'),
            'car_type_id'               => $request->input('car_type_id'),
            'fare'                      => $request->input('fare'),
            'order_date'                => Carbon::now(),
            'status'                    => config('define.status.reserve'),
            'valid'                     => 1
        ]);

        return response()->json([
            'order_id' => $order->id
        ], 200);
    }

    /**
     * UserGetOrderListAPI
     * @param $user_id $order_id
     * @desc | Get a list of order in a user
     */
    protected function getUserOrderList($user_id)
    {
        $orders = User::getOrders($user_id);
        if(empty($orders)) {
            return response()->json([
                'orders' => []
            ], 200);
        }
        return response()->json([
            'orders' => $orders
        ], 200);
    }

    /**
     * UserGetOrderDetailAPI
     * @param $user_id $order_id
     * @desc | Get Order detail
     */
    protected function getOrder($user_id, $order_id)
    {
        $refines['id'] = $order_id;
        $refines['user_id'] = $user_id;
        $order = Order::setFilter($refines)->first();

        if(!$order) {
            return response()->json('', 204);
        }

        return response()->json([
            'order' => $order
        ], 200);
    }

    /**
     * UserGetOrderSettlementAPI
     * @param $user_id $order_id
     * @desc | Settlement Report
     */
    protected function getOrderSettlement($user_id, $order_id)
    {
        $order = Order::setFilterForSettlement($user_id, $order_id)->firstOrFail();

        if ($order->transaction_response_code == config('define.authorize.response_code.approved')){
            return response()->json([
                'result' => config('define.result.success'),
                'message' => 'OK'
            ],200);
        }else{
            return response()->json([
                'result' => config('define.result.failure'),
                'message' => ''
            ], 200);
        }
    }

    /**
     * UserCancelOrderAPI
     * @param $user_id $order_id
     * @desc | To cancel an order in a user
     */
    protected function postOrderCancel($user_id, $order_id)
    {
        \DB::beginTransaction();

        $pay_info  = Payment::where('order_id',$order_id)
            ->where('type','authorize')
            ->firstOrFail();

        // cancel the order
        $refines['id']      = $order_id;
        $refines['user_id'] = $user_id;
        $refines['status']  = array('<', config('define.status.arrived'));
        $order              = Order::setFilter($refines)->firstOrFail();
        $cancel_fee         = \App\CarType::find($order->car_type_id)->firstOrFail(['cancel']);
        $payment            = Payment::createCapture($order_id);

        // Call CaptureAuth API
        $api_response = $this->authorize->captureAuth(
            $pay_info->transaction_id,
            $cancel_fee->cancel,
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

        $order->status = config('define.status.cancel');
        $order->cancel_date = Carbon::now();

        if(!$order->save() || !$payment->save()){
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        } else {
            \DB::commit();
            exec("php ".base_path("artisan")." push:cancel ".$order->id." > /dev/null 2>&1 &");
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }
    }

    /**
     * GetPaymentTokenAPI
     * @param $user_id $order_id
     * @desc | To get token of an order in a user
     */
    protected function getPaymentToken($user_id, $order_id)
    {
        // cancel the order
        $refines['id'] = $order_id;
        $refines['user_id'] = $user_id;
        $refines['status'] = ['<', config('define.status.payment_completion')];
        $order = Order::setFilter($refines)->firstOrFail();

        $payment = Payment::createAuth($order_id);

        if(!$payment->save()){
            return response()->json([
                'message' => trans('custom.error_occured')
            ], 400);
        }

        return response()->json([
            'token' => $payment->token
        ], 200 );
    }

    protected function postSetDevice($id, Request $request) {
        $user = \App\User::findOrFail($id);
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
        $user->device_id = $request->input('device_id');

        if(!$user->save()) {
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
