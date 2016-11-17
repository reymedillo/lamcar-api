<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Hash;
use App\Helpers;
use Auth;

class Admin extends Authenticatable
{
    
    protected $table = 'admins';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'password', 'valid'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public static function authGuardAttempt($request){
        return Auth::guard("admin")->attempt(self::credentials($request));
    }

    public static function authGuardUser(){
        return Auth::guard("admin")->user();
    }

    public static function credentials($request)
    {
        return [
            'name' => $request->input('name'),
            'password' => $request->input('password')
        ];
    }

    public static function validateLogin($request)
    {
        $helpers = new Helpers();
        $valid = $helpers->password_validators(new Validator);
        return $valid::make($request->all(), [
            'name' => 'required',
            'password' => 'required'
        ]);
    }
    
    public static function validateCreate($request)
    {
        $helpers = new Helpers();
        $valid = $helpers->password_validators(new Validator);
        return $valid::make($request->all(), [
            'number' => 'required|max:255|unique:cars',
            'password' => 'required|min:6|max:60|confirmed',
            'password_confirmation' => 'required|same:password'
        ]);
    }

    public static function validateRefresh($request)
    {
        return Validator::make($request->all(), [
            'refresh_token' => 'required'
        ]);
    }

    public function register($request)
    {
        \DB::beginTransaction();
         
        try{
            foreach($request->input() AS $k => $v){
                if(in_array($k, $this->getFillable())){
                    if($k == 'password'){
                        $v = Hash::make($v);
                    }
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
    
}
