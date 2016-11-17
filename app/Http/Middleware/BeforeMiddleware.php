<?php

namespace App\Http\Middleware;

use Closure;
use Route;
use DB;

class BeforeMiddleware
{
    public $client = array();
    
    public function handle($request, Closure $next)
    {
        \Log::info($request->url());
        \Log::info($request->all());

        $api_client = $this->api_client($request);
        
        if(count($api_client) <= 0){
            return response()->json(['message'  => "Unauthorized"],401);
        }

        $request['api_client_id'] = $api_client->id;
    
        return $next($request);
    }
    
    public function api_client($request, $onlyID = FALSE)
    {

        $api_client = \App\ApiClient::where('name',$request->input('client_name'))
                          ->where('secret',$request->input('client_secret'))
                          ->get();
        
        if($onlyID){
            return ($api_client->first()->id > 0 ? $api_client->first()->id : 0);
        }else{
            return $api_client->first();
        }
    }
    
}
