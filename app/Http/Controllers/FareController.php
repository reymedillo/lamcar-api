<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\CarType;
use Validator;

class FareController extends Controller
{
    protected function getShow(Request $request)
    {
        $validator = Validator::make($request->all(),[
                'distance' => 'required',
                ]);

        if($validator->fails()) {
            return response()->json(['message'=> trans('custom.fail_to',['name'=>'fare']),'errors'=>$validator->errors()],422);
        }

        $fares = CarType::getFares($request->input('distance'));

        if(!$fares) {
            return response()->json([
                'message' => trans('custom.error_occured'), 
            ], 400);
        }

        return response()->json([
            'fares'     => $fares, 
        ], 200);
    }
}
