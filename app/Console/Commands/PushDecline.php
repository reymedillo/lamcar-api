<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\AuthorizeNet;

class PushDecline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:decline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decline user requests.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AuthorizeNet $authorize)
    {
        parent::__construct();
        $this->authorize = $authorize;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("start push decline");

        $orders = \App\Order::select()
            ->where('status', config('define.status.payment_completion'))
            ->with(['payment' => function($query){
                $query->where('type','authorize');
            }])
            ->get();

        $date_now = \Carbon\Carbon::now();
        foreach($orders as $order) {
            $this->info("deal start order_id:".$order->id);
            if(count($order->payment) == 0) {
                $this->info("There is no payment data");
                continue;
            }
            $authPayment = $order->payment[0];
            $paydate_add_min = \Carbon\Carbon::parse($authPayment->payment_date);
            $paydate_add_min->addMinutes( config('define.void_add_minutes') );
            if($date_now > $paydate_add_min) {

                $this->info("db begin transaction");
                \DB::beginTransaction();

                $payment = \App\Payment::createVoid($order->id);

                // ## void here ##
                $api_response = $this->authorize->voidTransaction(
                    $authPayment->transaction_id,
                    $payment->id
                );

                if ($api_response === false || $api_response->isError()) {
                    $this->info("authorize void error");
                    \DB::rollBack();
                    continue;
                }

                $payment->payment_date = $date_now;
                $payment->transaction_response_code = $api_response->transactionResponse->responseCode;
                $payment->transaction_id = $api_response->transactionResponse->transactionId;
                $payment->api_response = json_encode( (array)$api_response );
                $payment->amount = $authPayment->amount;

                // ## push here ##
                $user = \App\User::find($order->user_id);
                $message = \Lang::get('custom.push_decline', [], $user->language);
                $option = ['order_id' => $order->id];

                if ($user->device_type == "iPhone") {
                    $message = \PushNotification::Message($message, [
                        'content-available' => 1,
                        'custom' => $option
                    ]);
                    $push = \PushNotification::app('ios_user');
                } elseif ($user->device_type == "android") {
                    $message = \PushNotification::Message($message, $option);
                    $push = \PushNotification::app('android');
                } else { 
                    $this->info("Device type error");
                    $this->info("db rollback");
                    \DB::rollBack();
                    continue;
                }
                $push->to($user->device_id)->send($message);
    
                //update status
                $order->status = config('define.status.declined');
                if(!$order->save() || !$payment->save()) {
                    $this->info("db rollback");
                    \DB::rollBack();
                } else {
                    $this->info("db commit");
                    \DB::commit();
                }
            }
        }

        return $this->info("end push decline");
    }

}
