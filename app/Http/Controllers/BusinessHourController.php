<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\BusinessHour;
use Validator;

class BusinessHourController extends Controller
{
    protected $business_hour;

    public function __construct(BusinessHour $business_hour)
    {
        $this->business_hour = $business_hour;
    }

    public function getIsopen()
    {
        $res = $this->business_hour->isOpen();
        return response()->json($res,200);
    }

    protected function getBusinessHr() {
        $business_hr = \App\BusinessHour::first(['time_zone','open_time','close_time']);
        if(count($business_hr)) {
            return response()->json(['business_hour'  => $business_hr],200);
        }
        return response()->json($business_hr,204);
    }

    protected function editBusinessHr(Request $request) {
        $validator = Validator::make($request->all(),[
                'time_zone' => 'required',
                'open_time' => 'required|date_format:"H:i:s"',
                'close_time' => 'required|date_format:"H:i:s"',
                ]);

        if($validator->fails()) {
            return response()->json(['message'=> trans('custom.fail_to',['name'=>'fare']),'errors'=>$validator->errors()],422);
        }
        \DB::beginTransaction();
        $check_empty = \App\BusinessHour::all();
        if(count($check_empty)) {
            $business_hr = \App\BusinessHour::first();
            $business_hr->time_zone     = $request->input('time_zone');
            $business_hr->open_time     = $request->input('open_time');
            $business_hr->close_time    = $request->input('close_time');
        } else {
            $business_hr = new \App\BusinessHour;
            $business_hr->time_zone     = $request->input('time_zone');
            $business_hr->open_time     = $request->input('open_time');
            $business_hr->close_time    = $request->input('close_time');
        }
        

        if($business_hr->save()) {
            \DB::commit();
            return response()->json(array('result' => 'success'), 200);
        }
        \DB::rollBack();
        return response()->json(['message' => trans('custom.error_occured') ], 400);
    }

}
