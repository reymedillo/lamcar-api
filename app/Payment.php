<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use App\CarType;
use App\Order;

class Payment extends Model
{

    protected $table = 'payments';
    
    protected $fillable = ['id','order_id','type','token','expired_date'];

    static function createAuth($order_id) {

        $order = Order::where('id', $order_id)
                      ->where('valid',1)
                      ->firstOrFail();

        $payment = self::firstOrNew(['order_id' => $order_id,
                                     'type' => 'authorize',
                                     'valid' => 1]);

        $exdate = Carbon::parse(date("Y-m-d H:i:s"));
        foreach(config('define.add_payment_token') as $k => $v){
            if($v == 0)continue;
            $exdate->{"add".ucfirst($k)}($v);
        }
        $payment->token = str_random(75);
        $payment->expired_date = $exdate->format('Y-m-d H:i:s');
        $payment->amount = $order->fare;

        return $payment;

    }

    static function createCapture($order_id, $flg=null) {

        $payment = self::firstOrNew(['order_id' => $order_id,
                                     'type' => 'capture',
                                     'valid' => 1]);

        $chargePayment = self::where('order_id', $order_id)
                             ->where('type', 'authorize')
                             ->where('valid',1)
                             ->firstOrFail();

        $refines['id'] = $order_id;
        $order = Order::setFilter($refines)->firstOrFail();

        $fare = CarType::where('id', $order->car_type_id)
                    ->where('valid',1)
                    ->firstOrFail();
        switch ($flg) {
          case config('define.status.dropoff'):
            $payment->amount = $chargePayment->amount;
            break;
          
          default:
            $payment->amount = $fare->cancel;
            break;
        }
        

        return $payment;

    }

    static function createVoid($order_id) {

        $chargePayment = self::where('order_id', $order_id)
                             ->where('type', 'authorize')
                             ->where('valid',1)
                             ->firstOrFail();

        $refines['id'] = $order_id;
        $order = Order::setFilter($refines)->firstOrFail();

        $payment = self::create(['order_id' => $order_id,
                                 'type' => 'void',
                                 'valid' => 1]);

        return $payment;

    }


    public function order() {
      return $this->belongsTo('App\Order');
    }

}
