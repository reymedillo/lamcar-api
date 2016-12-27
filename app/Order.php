<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;

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

    public static function getFilter($type=null) {
        return array (
            'orders.id',
            'orders.name', 
            'orders.contact', 
            (($type=='car')?'orders.pickup_location_en as pickup_location':'orders.pickup_location'),
            'orders.pickup_latitude',
            'orders.pickup_longitude',
            'orders.pickup_location_detail',
            (($type=='car')?'orders.dropoff_location_en as dropoff_location':'orders.dropoff_location'),
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

    public static function validateCreate($request)
    {

        Validator::extend('alpha_numeric_spaces', function($attribute, $value)
        {
            return preg_match('/^[a-zA-Z0-9\s]+$/u', $value);
        });
        Validator::extend('alpha_numeric_symbols', function($attribute, $value)
        {
            return preg_match('/^[a-zA-Z0-9\s\x21-\x2f\x3a-\x40\x5b-\x60\x7b-\x7e]+$/u', $value);
        });

        return Validator::make($request->all(),[
            'name' => 'required|alpha_numeric_spaces',
            'pickup_location' => 'required',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'pickup_location_detail' => 'alpha_numeric_symbols',
            'dropoff_location' => 'required',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
            'dropoff_location_detail' => 'alpha_numeric_symbols',
            'distance'  => 'required',
            'car_type_id'  => 'required',
            'fare'  => 'required'
        ]);

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

    public static function setFilter($refines = array(),$type=null) {

        $filter = self::getFilter($type);
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

    public static function findLocationEN($lat,$long) {
        $pickup = self::where('pickup_latitude', $lat)->where('pickup_longitude', $long)->whereNotNull('pickup_location_en')->first(['pickup_location_en']);
        if(count($pickup)) {
            return $pickup->pickup_location_en;
        }
        $dropoff = self::where('dropoff_latitude', $lat)->where('dropoff_longitude', $long)->whereNotNull('dropoff_location_en')->first(['dropoff_location_en']);
        if(count($dropoff)) {
            return $dropoff->dropoff_location_en;
        }
        return config('define.valid.false');
    }

    public static function connectGoogleMap($lat,$long) {
        $url = sprintf('%s?latlng=%.f,%.f&key=%s&language=en&region=US',
            config('google-geocoder.requestUrl'),
            $lat,
            $long,
            config('google-geocoder.applicationKey')
        );

        try {
            $api = file_get_contents($url);
            $res = json_decode($api, true);
            return $res['results'][0]['formatted_address'];
        } catch (\Exception $e) {
            \Mail::send('email.google.error', [
                    'title' => trans('custom.fail_to',['name'=>'connect to API.']),
                    'lat' => $lat,
                    'long' => $long,
                    'api_url' => $url
                ], 
                function ($m) {
                    $m->from(env('MAIL_USERNAME'));
                    $m->to(env('MAIL_USERNAME'), 'Administrator')->subject(trans('custom.error_occured'));
            });
            return null;
        }
    }

    public static function checkLocationEN($order,$name) {
        if(!preg_match('/^[a-zA-Z0-9\s\x21-\x2f\x3a-\x40\x5b-\x60\x7b-\x7e]+$/u', $order->{$name.'_location'})) {
            $find_location_en = self::findLocationEN($order->{$name.'_latitude'},$order->{$name.'_longitude'});
            if($find_location_en === config('define.valid.false')) {
                \Log::info('connecting to GOOGLE MAP API');
                $google_map = self::connectGoogleMap($order->{$name.'_latitude'},$order->{$name.'_longitude'});
                return $google_map;
            } else {
                return $find_location_en;
            }
        } else {
            return $order->{$name.'_location'};
        }
    }

    public function payment() {
        return $this->hasMany('App\Payment','order_id','id');
    }
    
}

