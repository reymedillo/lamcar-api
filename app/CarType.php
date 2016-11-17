<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CarType extends Model
{

    protected $table = "car_types";

    /**
     * Get Fare by distance
     * @param distance
     * return fare else return false if the fare is not valid
     */
    static function getFares($distance) {

        // get fare by distance
        $carTypes = DB::table('car_types')
            ->select('id', 'name_'.\App::getLocale().' as name', 'base', 'per_mile', 'seat_num')
            ->where('valid', 1)
            ->get();

        if(empty($carTypes))
            return false;

        $fares = array();
        foreach($carTypes as $carType){
            $fares[] = [
                "car_type_id" => $carType->id,
                "car_type_name" => $carType->name . "(" . trans('custom.max_people_num',['num' => $carType->seat_num]) . ")",
                "fare" => $carType->base + floor($distance) * $carType->per_mile
            ];
        }

        return $fares;
    }

}
