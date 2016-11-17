<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Order;
use App\Car;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Admin;
use Validator;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Helpers;
use Symfony\Component\Console\Output\StreamOutput;

class AdminController extends Controller
{

    protected $user;
    
    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Guard $guard, Admin $admin)
    {
        
        $this->admin       = $admin;
        
        $this->guard        = $guard;
        
        $this->current_date = date('Y-m-d H:i:s'); 
          
    }
        
    /**
     * Create a User
     * @param $request
     * @desc Create New User
     */
    protected function postCreate(Request $request)
    {
        //Check if already exist
        $validator = $this->admin->validateCreate($request());
        if($validator->fails()){
            return response()->json([
                'message' => trans('custom.fail_to',['name'=>'register']),
                'errors' => $validator->errors()
            ], 422);
        }
        
        //Register New Admin
        if($this->admin->register($request)){
            $userAuth = new AuthController(Auth::guard('admin'));
            return $userAuth->postLogin($request);
        }else{
            return response()->json([
                'message' => trans('custom.error_occured'),
            ], 400);
        }
    }

    public function Testpush(Request $request) {
        $accept = $arrive = $cancel = $decline = $request_push = null;

        if( !empty($request->input('push')) ) {
            foreach($request->input('push') as $input ) {
                switch ($input) {
                    case 'ACCEPT':
                        //push accept
                        $accept = shell_exec("php ".base_path("artisan")." push:accept ". $request->input('order_id') ." | sed 's/\x1B\[\([0-9]\{1,2\}\(;[0-9]\{1,2\}\)\?\)\?[mGK]//g' ");
                        break;
                    case 'ARRIVE':
                        //push arrive
                        $arrive = shell_exec("php ".base_path("artisan")." push:arrive ". $request->input('order_id') ." | sed 's/\x1B\[\([0-9]\{1,2\}\(;[0-9]\{1,2\}\)\?\)\?[mGK]//g' ");
                        break;
                    case 'CANCEL':
                        //push cancel
                        $cancel = shell_exec("php ".base_path("artisan")." push:cancel ". $request->input('order_id') ." | sed 's/\x1B\[\([0-9]\{1,2\}\(;[0-9]\{1,2\}\)\?\)\?[mGK]//g' ");
                        break;
                    case 'DECLINE':
                        //push decline
                        $decline = shell_exec("php ".base_path("artisan")." push:decline | sed 's/\x1B\[\([0-9]\{1,2\}\(;[0-9]\{1,2\}\)\?\)\?[mGK]//g' ");
                        break;
                    case 'REQUEST':
                        //push request
                        $request_push = shell_exec("php ".base_path("artisan")." push:request ". $request->input('order_id') ." | sed 's/\x1B\[\([0-9]\{1,2\}\(;[0-9]\{1,2\}\)\?\)\?[mGK]//g' ");
                        break;
                    
                    default:
                        break;
                }
            }
        }
        
        return response()->json(['api'=>
            [
                'ACCEPT' => $accept,
                'ARRIVE' => $arrive,
                'CANCEL' => $cancel,
                'DECLINE'=> $decline,
                'REQUEST'=> $request_push
            ]
        ],200);
        
    }
    
}
