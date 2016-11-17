<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Order extends Model
{
    protected $table = "orders";

    protected $fillable = [
        'user_id', 
        'name', 
        'contact', 
        'pickup_location', 
        'pickup_latitude',
        'pickup_longitude', 
        'pickup_location_detail',
        'dropoff_location', 
        'dropoff_latitude',
        'dropoff_longitude', 
        'distance',
        'car_type_id',
        'fare',
        'order_date',
        'status',
        'valid',
    ];

    public static function getFilter() {
        return array (
            'orders.id',
            'orders.name', 
            'orders.contact', 
            'orders.pickup_location',
            'orders.pickup_latitude',
            'orders.pickup_longitude',
            'orders.pickup_location_detail',
            'orders.dropoff_location',
            'orders.dropoff_latitude',
            'orders.dropoff_longitude', 
            'orders.dropoff_date',
            'orders.distance',
            'orders.car_type_id',
            'orders.pickup_scheduled_date',
            'orders.fare',
            'orders.order_date',
            'orders.car_id',
            'orders.status'
        );
    }

    public static function refineForCar($orders) {

        foreach($orders as $key => $order) {
            $car = Car::find($order->car_id);
            
            unset($orders[$key]->car_id);
            if(!$car) {
                $orders[$key]->car_number = '';
            } else {
                $orders[$key]->car_number = $car->number;
            }
        }

        if(count($orders) > 1) {
            return $orders;
        } else if(count($orders) == 1) {
            return $orders[0];  
        }
        
        return array();
    }

    public static function setFilter($refines = array()) {

        $filter = self::getFilter();
        $filter[] = "car_types.name_".\App::getLocale()." as car_type_name";
        $filter[] = "cars.number as car_number";
        $filter[] = "drivers.name as driver_name";

        $query = parent::__callStatic("select", $filter)
            ->join('car_types', 'orders.car_type_id', '=', 'car_types.id')
            ->leftJoin('cars', 'orders.car_id', '=', 'cars.id')
            ->leftJoin('drivers', 'orders.driver_id', '=', 'drivers.id')
            ->where('orders.valid',1);

        foreach($refines as $col => $refine){
            if(is_array($refine)){
                if(count($refine) == 2){
                    $query->where('orders.'.$col, $refine[0], $refine[1]);
                }else{
                    $key = key($refine);
                    $query->{$key}('orders.'.$col, $refine[$key]);
                }
            }else{
                $query->where('orders.'.$col, $refine);
            }
        }
        return $query;
         
    }

    public static function setFilterForSettlement($user_id, $order_id)
    {
        $query = parent::__callStatic("select", ['payments.transaction_response_code'])
             ->join('payments', 'orders.id', '=', 'payments.order_id')
             ->where('orders.id', $order_id)
             ->where('orders.user_id', $user_id)
             ->where('orders.valid',1)
             ->where('payments.type', 'authorize')
             ->where('payments.valid',1);
        return $query;
    }

    public static function getOrders($request) {
         
        $offset = 15;
         
        if($request->input('offset')){
            $offset = $request->input('offset');
        }

        if(!$request->input('status') || $request->input('status') == 'current') {
            $status[] = config('define.status.payment_completion');
            $status[] = config('define.status.accept');
            $status[] = config('define.status.arrived');
            $status[] = config('define.status.pickup');
        } else {
            $status[] = config('define.status.payment_completion');
            $status[] = config('define.status.accept');
            $status[] = config('define.status.arrived');
            $status[] = config('define.status.pickup');
            $status[] = config('define.status.dropoff');
            $status[] = config('define.status.declined');
            $status[] = config('define.status.cancel');
        }
        
        $refines['status'] = ['wherein' => $status];
        $orders = self::setFilter($refines)
            ->orderBy('order_date', 'desc')
            ->paginate($offset);

        return $orders;
         
    }

    public function payment() {
        return $this->hasMany('App\Payment','order_id','id');
    }
    
}

