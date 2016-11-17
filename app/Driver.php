<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use DB;

class Driver extends Authenticatable
{

    protected $table = "drivers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login_id', 'password', 'name', 'valid'
    ];

    public static function credentials($request)
    {
        return [
            'login_id' => $request->input('login_id'),
            'password' => $request->input('password')
        ];
    }

    public static function validateLogin($request)
    {
        Validator::extend('assigned', function($attribute, $value, $parameters, $validator) {
            $driver = DB::table("drivers")
                ->join('cars', 'drivers.id', '=', 'cars.driver_id')
                ->where('login_id', $value)
                ->first();

            if($driver == null){
                return false;
            }else{
                return true;
            }
        });
        return Validator::make($request->all(), [
            'login_id' => 'required|max:255|exists:drivers,login_id|assigned',
            'password' => 'required',
            'device_id' => 'required',
            'device_type' => 'required',
        ]);
    }

    public static function validateInput($request) {
        if($request->has('password') || $request->has('password_confirmation')) {
            $validator = \Validator::make($request->all(), [
                'login_id' => 'required',
                'name' => 'required',
                'password' => 'required|min:6|max:60|confirmed',
                'password_confirmation' => 'required'
            ]);
        } else {
            $validator = \Validator::make($request->all(), [
                'login_id' => 'required',
                'name' => 'required'
            ]);
        }

        return $validator;
    }

    public static function getVacantDrivers() {
        $hired_drivers = array();
        $temp_hired_drivers = \App\Car::whereNotNull('driver_id')->get(['driver_id']);

        foreach($temp_hired_drivers as $hash) {
            $hired_drivers[] = $hash->driver_id;
        }
        $vacant_drivers = \App\Driver::whereNotIn('id', $hired_drivers)->where('valid', true)->get(['id','name']);

        return $vacant_drivers;
    }

    public static function getDrivers($request) {
        $perPage = 15;
        if($request->has('perPage')) {
            $perPage = $request->input('perPage');
        }

        if($request->has('search')) {
            $like = $request->input('search');

            $drivers = self::where('drivers.valid', config('define.valid.true'))
                    ->where('name', 'LIKE', "%$like%")
                    ->leftJoin('cars', 'cars.driver_id', '=', 'drivers.id')
                    ->leftJoin('car_types', 'car_types.id', '=', 'cars.car_type_id')
                    ->select('drivers.id as id', 'login_id', 'drivers.name as name', 'cars.number as car_number', 'car_types.name_'.\App::getLocale().' as car_type_name')
                    ->paginate($perPage);
        } else {
            $drivers = self::where('drivers.valid', config('define.valid.true'))
                    ->leftJoin('cars', 'cars.driver_id', '=', 'drivers.id')
                    ->leftJoin('car_types', 'car_types.id', '=', 'cars.car_type_id')
                    ->select('drivers.id as id', 'login_id', 'drivers.name as name', 'cars.number as car_number', 'car_types.name_'.\App::getLocale().' as car_type_name')
                    ->paginate($perPage);
        }

        return $drivers;
    }
}
