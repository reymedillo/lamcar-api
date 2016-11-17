<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Route;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guard('api')->guest()) {
            return response()->json(['message'  => "Unauthorized."],401);
        } else {
            if (Auth::guard('api')->check()) {
                $access_token = Auth::guard('api')->user();
                // check of role
                if($access_token->role != $role){
                    return response()->json(['message'  => "Unauthorized."],401);
                }
                // check of expired date
                if (Route::getCurrentRoute()->getName() != $role.'.refresh' &&
                    strtotime($access_token->expired_date) + config('define.token_extension_time') < time()) {
                    return response()->json(['message'  => "Unauthorized."],401);
                }
                // check of api_client_id
                $middleware = new BeforeMiddleware();
                $api_client = $middleware->api_client($request);
                if ($access_token->api_client_id != $api_client->id) {
                    return response()->json(['message'  => "Unauthorized."],401);
                }
                // check of account_id
                if ($role != "admin"){
                    if ($access_token->account_id != $request->route('id')) {
                        return response()->json(['message'  => "Unauthorized."],401);
                    }
                }
                $request['role'] = $access_token->role;
                $request['api_client_id'] = $access_token->api_client_id;
                $request['account_id'] = $access_token->account_id;
                return $next($request);
            }
        }
        return response()->json(['message'  => "Unauthorized."],401);
    }
}
