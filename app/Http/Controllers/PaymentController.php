<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Order;
use App\Payment;
use App\Fare;
use App\User;
use App\BusinessHour;
use Carbon\Carbon;
use Validator;
use App\AuthorizeNet;

class PaymentController extends Controller
{
    protected $payment;

    public function __construct(Payment $payment, AuthorizeNet $authorize) {
        $this->payment = $payment;
        $this->authorize = $authorize;
    }

    /**
     * GetPaymentAPI
     * @param $token
     * @desc | To get payment info
     */
    protected function getShow($token)
    {
        $columns = ['payments.id', 'payments.order_id',
                    'payments.amount', 'payments.token',
                    'payments.payment_date', 'payments.transaction_response_code',
                    'payments.expired_date',
                    'pickup_location', 'dropoff_location',
                    'distance', 'orders.fare','orders.status'];
        $payment = Payment::select($columns)
                          ->join('orders', 'payments.order_id', '=', 'orders.id')
                          ->where('payments.token', $token)
                          ->where('payments.valid',1)
                          ->firstOrFail();

        if($payment->expired_date < Carbon::now()){
            return response()->json(['message' => trans('custom.expired')], 403);
        }else{
            return response()->json(['payment' => $payment], 200);
        }
    }

    /**
     * GetResultPaymentAPI
     * @param $token
     * @desc | To get payment info
     */
    protected function getResult($token)
    {
        $payment = Payment::select('payments.transaction_response_code')
                          ->join('orders', 'payments.order_id', '=', 'orders.id')
                          ->where('payments.token', $token)
                          ->where('payments.valid',1)
                          ->firstOrFail();

        if($payment->transaction_response_code == config('define.authorize.response_code.approved')){
            return response()->json(array('result' => 'success'), 200);
        }else{
            return response()->json(array('result' => 'failure'), 200);
        }
    }


     /**
     * PaymentAPI
     * @param $token
     * @desc | To get payment info
     */
    protected function postCharge($token, Request $request)
    {
        $res = BusinessHour::isOpen();
        if(!$res->result){
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'payment']),
                'errors' => ['response' => [$res->message]]
            ], 412);
        }
        $validator = Validator::make($request->all(),[
                'email'    => 'email',
                'cardNumber' => 'required',
                'cardExpirationDate' => 'required',
                'cardCode' => 'required',
                'cardName' => 'required',
                'cardName' => 'required',
                'service_term' => 'required'
        ]);
        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'payment']),
                'errors' => $validator->errors()
            ], 422);
        }

        $email = ($request->has('email'))?$request->input('email'):' ';

        \DB::beginTransaction();

        $payment = Payment::where('token', $token)
                          ->where('type', 'authorize')
                          ->firstOrFail();

        if($payment->transaction_response_code == config('define.authorize.response_code.approved')){
            \DB::rollBack();
            return response()->json(array('result' => 'success'), 200);
        }

        if($payment->expired_date < Carbon::now() ||
           !empty($payment->transaction_response_code)) {
            \DB::rollBack();
            return response()->json(['message' => trans('custom.errors.expired')], 403);
        }

        $order = Order::where('id', $payment->order_id)
                      ->where('status', config('define.status.reserve'))
                      ->where('valid', 1)
                      ->firstOrFail();
        $user = User::where('id', $order->user_id)->where('valid', 1)->firstOrFail();

        // AuthorizeNet Save or Edit Profile
        if($user->customer_profile_id == NULL || $user->customer_payment_profile_id == NULL) {
            $profile = $this->authorize->saveProfileTo(
                $request->input('cardNumber'),
                $request->input('cardExpirationDate'),
                $request->input('cardCode'),
                $user->id,
                $email
            );
        } else {
            $profile = $this->authorize->updateProfile(
                $request->input('cardNumber'),
                $request->input('cardExpirationDate'),
                $request->input('cardCode'),
                $user->id,
                $email,
                $user->customer_profile_id,
                $user->customer_payment_profile_id
            );
        }

        if ($profile->isError()) {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'payment']),
                'errors' => $profile->getErrors()
            ], 422);
        } else {
            $user->customer_profile_id = $profile->customer_profile_id;
            $user->customer_payment_profile_id = $profile->customer_payment_profile_id;
            if(!$user->save()) {
                \DB::rollBack();
                return response()->json([
                    'message' => trans('custom.errors.occurred')
                ], 400);
            }else{
                \DB::commit();
            }
        }

        \DB::beginTransaction();

 
        // TODO Call AuthOnly API
        $api_response = $this->authorize->authorizeOnly(
            $profile->customer_profile_id,
            $profile->customer_payment_profile_id,
            $payment->amount,
            $payment->id
        );

        if ($api_response === false) {
            \DB::rollBack();
            return response()->json(['message' => trans('custom.error_occured') ], 400);
        }

        if ($api_response->isError()) {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'payment']),
                'errors' => $api_response->getErrors()
            ], 422);
        }

        $order->status = config('define.status.payment_completion');
        $payment->payment_date = Carbon::now();
        $payment->transaction_response_code = 1;
        $payment->transaction_id = $api_response->transactionResponse->transactionId;
        $payment->api_response = json_encode( (array)$api_response );

        if( !$payment->save() || !$order->save()) {
            \DB::rollBack();
            return response()->json(['message' => trans('custom.error_occured') ], 400);
        }else{
            \DB::commit();
            exec("php ".base_path("artisan")." push:request ".$order->id." > /dev/null 2>&1 &");
            return response()->json(array('result' => 'success'), 200);
        }
    }

}
