<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class BusinessHour extends Model
{
    protected $table = "business_hours";

    static function isOpen(){
        $res = new \stdClass();
        $data = self::where('valid', 1)->firstOrFail();
        if ($data->open_time <= Carbon::now($data->time_zone)->format('H:i:s') &&
            $data->close_time >= Carbon::now($data->time_zone)->format('H:i:s')) {
            $res->result = true;
            return $res;
        }else{
            $res->result = false;
            $res->message = 
                trans('custom.closed',
                    [
                        'open' => Carbon::parse($data->open_time)->format('G:i'),
                        'close' => Carbon::parse($data->close_time)->format('G:i'),
                    ]
                );
            return $res;
        }
    }
}
