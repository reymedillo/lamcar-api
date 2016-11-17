<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Route;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\User;
use App\Car;
use App\AccessToken;

class AuthController extends Controller
{
    protected $auth;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  LoginRequest  $request
     * @return Response
     */
    protected function postLogin(Request $request)
    {
        switch(Route::getCurrentRoute()->getName()){
            case 'admin.login':
                $role = 'admin';
                $name = 'name';
                break;
            case 'car.login':
                $role = 'car';
                $name = 'number';
                break;
            default:
                return response()->json([
                    'message'  => trans('custom.error_occurred')
                ], 400);
        }

        $className = 'App\\'.ucfirst($role);

        $validator = $className::validateLogin($request);
        if($validator->fails()){
            return response()->json([
                'message'=> trans('custom.fail_to',['name'=>'login']),
                'errors' => $validator->errors()
            ], 422);
        }

        if ($className::authGuardAttempt($request)) {
            $account = $className::authGuardUser(); 
            $request['role'] = $role;
            $request['account_id'] = $account->id;

            $login_token = AccessToken::createTokens($request);  //save to db
            if($login_token == false) {
                return response()->json([
                    'message'  => trans('custom.error_occurred')
                ], 400);
            }
            if($role == 'car'){
                $update_result = Car::updateDevices($request);
                if($update_result == false) {
                    return response()->json([
                        'message'  => trans('custom.error_occurred')
                    ], 400);
                }
            }

            return response()->json([// response from db
                'access_token'  => $login_token['api_token'],
                'expired_date'  => $login_token['expired_date'],
                'refresh_token' => $login_token['refresh_token'],
                $role.'_id'     => $account->id,
                $role.'_'.$name => $account->{$name},
            ], 200);

        }else{
            return response()->json([
                'message'=> trans('custom.fail_to',['name'=>'login']),
                'errors' => [
                    'auth' => [trans('auth.failed')]
                ]
            ], 422);
        }

    }

    protected function postRefresh(Request $request)
    {
        $class_name = 'App\\'.ucfirst($request->role);
        $validator = $class_name::validateRefresh($request);
        if($validator->fails()){
            return response()->json([
                'message' => trans('custom.error_occured'),
                'errors'  => $validator->errors()
            ] , 422 );
        }

        $access_token = $this->auth->user();

        if ($request->role != "user"){
            $refresh_token_expire_date = $access_token->refresh_token_expired_date;
            if (strtotime($refresh_token_expire_date) + config('define.token_extension_time') < time()) {
                return response()->json([
                    'message'  => trans('custom.fail_to',['name'=>'refresh token'])
                ], 401);
            }
        }

        //update to db
        if($access_token->refresh_token == $request->input('refresh_token')) {
            \DB::beginTransaction();
            try{
                $new_token = AccessToken::generateToken($request->role);
                $access_token->api_token = $new_token['api_token'];
                $access_token->expired_date = $new_token['expired_date'];
                $access_token->save();
                if ($request->role == "user"){
                    $user = $class_name::findOrFail($request->account_id);
                    $user->language = $request->input('language');
                    $user->save();
                }
                \DB::commit();
                return response()->json([ //response from db
                    'access_token' => $access_token->api_token,
                    'expired_date' => $access_token->expired_date,
                ], 200);
            }catch(Exception $e){
                \DB::rollBack();
                return response()->json([
                    'message'  => trans('custom.error_occurred')
                ], 400);
            }
        }
        return response()->json([
            'message'  => trans('custom.error_occurred')
        ], 400);
    }

    protected function postLogout(Request $request, $id)
    {
        if($request->role == "car"){
            $request->device_id = "";
            $request->device_type = "";
            $carResults = Car::updateDevices($request);
        }
        return AccessToken::updateStatusByAccountId($id,$request->input('role'));
    }
}
