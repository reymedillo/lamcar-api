<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;

class User extends Authenticatable
{
    
    protected $table = "users";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'device_id', 'device_type', 'language', 'valid','customer_profile_id','customer_payment_profile_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'api_token', 'expired_date'
    ];
        
    public static function validateCreate($request)
    {
        return Validator::make($request->all(), [
            'device_id' => 'required|max:255',
            'device_type' => 'required|max:255',
            'language' => 'required|in:ja,en',
        ]);
    }
    
    public static function validateRefresh($request)
    {
        return Validator::make($request->all(), [
            'refresh_token' => 'required',
            'language' => 'required|in:ja,en'
        ]);
    }

    public static function checkUserInput($request)
    {
        return \App\User::where('device_id', $request->input('device_id'))
            ->where('device_type', $request->input('device_type'))
            ->where('valid', true)
            ->first();
    }

    public function register($request)
    {
        \DB::beginTransaction();
        
        try{
             
            foreach($request->input() AS $k => $v){
                if(in_array($k, $this->getFillable())){
                    $this->{$k} = $v;
                }
            }
                                                                               
            if($this->save()){
                \DB::commit();
                return true;
            }else{
                \DB::rollBack();
                return false;
            }
        
        }catch(Exception $e){
            \DB::rollBack();
            return false;
        }
    }

    public static function getOrders($user_id) {
        
        $refines['user_id'] = $user_id;
        $refines['status'] = ['>', config('define.status.reserve')];
        $orders = Order::setFilter($refines)
            ->orderby('id', 'desc')
            ->get();
                  
        if(count($orders) <= 0)
            return array();
            
        return $orders;
        
    }
}
