<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Hash;
use Validator;
use DB;
use App\Helpers;
use Auth;
use App\Driver;

class Car extends Model
{

    protected $table = "cars";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'car_type_id', 'driver_id', 'note', 'device_id', 'device_type', 'valid'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public static function authGuardAttempt($request){
        return Auth::guard("driver")->attempt(Driver::credentials($request));
    }

    public static function authGuardUser(){
        $driver = Auth::guard("driver")->user();
        return self::where('driver_id', $driver->id)->first();
    }

    public static function validateLogin($request)
    {
        return Driver::validateLogin($request);
    }

    public static function validateCreate($request)
    {
        $helpers = new Helpers();
        $valid = $helpers->password_validators(new Validator);
        return $valid::make($request->all(), [
            'number' => 'required|max:255|unique:cars',
            'car_type_id' => 'required'
        ]);
    }

    public static function validateUpdate($request,$id)
    {
        $current_driver = self::find($id);
        $helpers = new Helpers();
        $valid = $helpers->password_validators(new Validator);

        $validator = $valid::make($request->all(), [
            'number' => 'required|max:255|unique:cars,number,'.$id,
            'car_type_id' => 'required'
        ]);

        return $validator;
    }
    
    public static function validateRefresh($request)
    {
        return Validator::make($request->all(), [
            'refresh_token' => 'required'
        ]);
    }

    public function register($request){

        \DB::beginTransaction();

        try{

            foreach($request->input() AS $k => $v){
                if(in_array($k, $this->getFillable())){
                    $this->{$k} = $v;
                }
            }

            if($this->save()){
                \DB::commit();
                return $this->id;
            }else{
                \DB::rollBack();
                return 0;
            }

        }catch(Exception $e){
            \DB::rollBack();
            return 0;
        }

    }

    public function edit($request)
    {
        if ($request->id > 0) {

            $fields = array();

            foreach($request->input() AS $k => $v){
                if(in_array($k,$this->getFillable())){
                    $fields[$k] = ($v=='NULL')?NULL:$v;
                }
            }

            $car = $this->where('id', $request->id)->update($fields);

            if($car > 0){
                return $car;
            }else{
                return null;
            }

        }else{
            return null;
        }
    }

    public static function updateDevices($request)
    {
        \DB::beginTransaction();

        $inputData = self::where('id', $request->account_id)->where('valid', true);
        $deviceObj = $inputData->update([
            'device_id' => $request->input('device_id'),
            'device_type' => $request->input('device_type'),
        ]);

        if(!$deviceObj) {
            \DB::rollBack();
            return FALSE;
        } else {
            \DB::commit();
            return TRUE;
        }
    }

    public static function getCars($params) {

        $perPage = 15;

        if(isset($params['perPage']))

            $perPage = $params['perPage'];

        if(isset($params['search'])) {

            $like = $params['search'];
            $cars = DB::table('cars')
                    ->join('car_types', 'cars.car_type_id','=','car_types.id')
                    ->leftJoin('drivers', 'cars.driver_id','=','drivers.id')
                    ->select(DB::raw('cars.id,car_types.name_'.\App::getLocale().' as car_type_name ,number, drivers.id as driver_id, drivers.name as driver_name, note'))
                    ->where('cars.number', 'LIKE', "%$like%")
                    ->where('cars.valid', '=', config('define.valid.true'))
                    ->paginate($perPage);

        } else {
            $cars = DB::table('cars')
                    ->join('car_types', 'cars.car_type_id','=','car_types.id')
                    ->leftJoin('drivers', 'cars.driver_id','=','drivers.id')
                    ->select(DB::raw('cars.id,car_types.name_'.\App::getLocale().' as car_type_name ,number, drivers.id as driver_id, drivers.name as driver_name, note'))
                    ->where('cars.valid', '=', config('define.valid.true'))
                    ->paginate($perPage);

        }

        $pagination = clone($cars);

        $pagination = $pagination->toArray();

        unset($pagination['data']);

        $cars = $cars->getCollection()->all();

        return array('cars' => $cars, 'pagination' => $pagination);

    }

    public static function getAvailableCars($latitude, $longitude, $type = null)
    {
        //check hired cars
        $hired = [];
        $check_hired = \App\Order::whereIn(
            'status', [
                config('define.status.accept'),
                config('define.status.arrived'),
                config('define.status.pickup')
            ]
        )->distinct('car_id')->get(['car_id']);

        foreach($check_hired as $car) {
            $hired[] = $car->car_id;
        }

        //check cars available within miles
        $cars = \App\Car::where('device_id', '<>', '')
            ->where('device_type','<>' ,'') 
            ->where('latitude','<>' , '')
            ->where('longitude','<>' , '')
            ->where('location_update_date','>',\Carbon\Carbon::now()->subSeconds(config('define.seconds_available')) )
            ->whereNotIn('id',$hired)
            ->where('valid',true);

        if(!is_null($type)){
            $cars->where('car_type_id', $type);
        }

        $cars = $cars->get(['id','car_type_id','device_type','device_id','latitude','longitude']);

        if(count($cars) == 0) {
           return [];
        }

        $available = [];
        foreach ($cars as $car) {
            $theta = $longitude - $car->longitude;
            $dist = sin(deg2rad($latitude)) * sin(deg2rad($car->latitude)) +  cos(deg2rad($latitude)) * cos(deg2rad($car->latitude)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;

            if($miles <= config('define.miles_available') ) {
                $available[] = (object) [
                    'id'          => $car->id,
                    'car_type_id' => $car->car_type_id,
                    'device_type' => $car->device_type,
                    'device_id'   => $car->device_id,
                    'latitude'    => $car->latitude,
                    'longitude'   => $car->longitude
                ];
            }
        }

        return $available;
    }

}
