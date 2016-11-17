<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class DriverController extends Controller
{
    //
    protected function getIndex(Request $request) {
        $pagination = null;

        if($request->has('list')) {
            $drivers = \App\Driver::getVacantDrivers();
        } else {
            $drivers = \App\Driver::getDrivers($request);
            $pagination = clone($drivers);
            unset($pagination['data']);

            $drivers = $drivers->getCollection()->all();
        }

        return response()->json([
            'drivers'=> $drivers,
            'pagination' => $pagination
        ], 200);
    }

    protected function postCreate(Request $request) {
        $validator = \App\Driver::validateInput($request);

        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'register']),
                'errors' => $validator->errors()
            ],422);
        }

        $driver = new \App\Driver;
        \DB::beginTransaction();
        $driver->login_id = $request->input('login_id');
        $driver->name     = $request->input('name');
        $driver->password = \Hash::make($request->input('password'));

        if($driver->save()) {
            \DB::commit();
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        } else {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'register']),
                'errors' => 'Error'
            ],400);
        }
    }

    protected function getEdit($id) {
        $driver = \App\Driver::leftJoin('cars', 'cars.driver_id', '=', 'drivers.id')
            ->leftJoin('car_types', 'car_types.id', '=', 'cars.car_type_id')
            ->where('drivers.id', $id)
            ->where('drivers.valid', config('define.valid.true'))
            ->first(['drivers.id as id', 'login_id', 'drivers.name as name', 'cars.number as car_number', 'car_types.name_'.\App::getLocale().' as car_type_name']);
        return response()->json([
            'driver'=> $driver
        ], 200);
    }

    protected function putEdit(Request $request, $id) {
        $validator = \App\Driver::validateInput($request);

        if($validator->fails()) {
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'update']),
                'errors' => $validator->errors()
            ],422);
        }

        $driver = \App\Driver::find($id);
        \DB::beginTransaction();
        $driver->login_id = $request->input('login_id');
        $driver->name     = $request->input('name');
        $driver->password = \Hash::make($request->input('password'));

        if($driver->save()) {
            \DB::commit();
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        } else {
            \DB::rollBack();
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'update']),
                'errors' => 'Error'
            ],400);
        }
    }

    protected function deleteDestroy($id) {
        $driver             = \App\Driver::findOrFail($id);
        $driver->valid      = config('define.valid.false');

        if(!$driver->save()) {
            return response()->json([
                'message'=>trans('custom.error_occured')
            ], 400);
        }else{
            return response()->json([
                'result' => config('define.result.success')
            ], 200);
        }
    }

}
