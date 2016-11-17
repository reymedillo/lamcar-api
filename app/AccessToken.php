<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use App\Http\Middleware\BeforeMiddleware;
use App\Scopes\ValidScope;

class AccessToken extends Authenticatable
{

    protected $table = 'access_tokens';
    
    protected $fillable = [
        'id',
        'api_token',
        'expired_date',
        'refresh_token',
        'refresh_token_expired_date',
        'account_id',
        'api_client_id',
        'role',
        'valid',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ValidScope);
    }

    protected function access_token()
    {
        return $this->hasOne('App\AccessToken');
    }

    public static function createTokens($request)
    {
        \DB::beginTransaction();

        try{
            AccessToken::where('account_id', $request->account_id)
                    ->where('role', $request->role)
                    ->where('api_client_id', $request->api_client_id)
                    ->where('valid', 1)
                    ->update(['valid' => 0]);
        }catch(\Exception $e){
            \DB::rollBack();
            return false;
        }

        $token_data = self::generateToken($request->role);
        $new_access_token = AccessToken::create([
            'api_token'                  => $token_data['api_token'],
            'expired_date'               => $token_data['expired_date'],
            'refresh_token'              => $token_data['refresh_token'],
            'refresh_token_expired_date' => $token_data['refresh_token_expired_date'],
            'role'                       => $request->role,
            'account_id'                 => $request->account_id,
            'api_client_id'              => $request->api_client_id,
            'valid'                      => true,
        ]);

        if(!$new_access_token) {
            \DB::rollBack();
            return false;
        } else {
            \DB::commit();
            return [
                'api_token'       => $new_access_token->api_token,
                'expired_date'    => $new_access_token->expired_date,
                'refresh_token'   => $new_access_token->refresh_token,
            ];
        }
    }

    public static function generateToken($role)
    {
        $api_token = str_random(75);
        $refresh_token = str_random(75);
        $ap_exdate = \Carbon\Carbon::now();
        foreach(config('define.add_api_token') as $k => $v){
            if($v == 0)continue;
            $ap_exdate->{"add".ucfirst($k)}($v);
        }
        $expired_date = $ap_exdate->format('Y-m-d H:i:s');
        $rt_expired_date = null;
        if($role == 'car' || $role == 'admin'){
            $rt_exdate = \Carbon\Carbon::now();
            foreach(config('define.add_refresh_token') as $k => $v){
                if($v == 0)continue;
                $rt_exdate->{"add".ucfirst($k)}($v);
            }
            $rt_expired_date = $rt_exdate->format('Y-m-d H:i:s');
        }
    
        return [
            'api_token' => $api_token,
            'refresh_token' => $refresh_token,
            'expired_date' => $expired_date,
            'refresh_token_expired_date' => $rt_expired_date
        ];
         
    }
    
    public static function updateStatusByAccountId($id,$role)
    {
        \DB::beginTransaction();
         
        $api_id = self::where('account_id',$id)
                      ->where('role',$role)
                      ->where('valid',true)
                      ->update(['valid' => 0]);

        if($api_id <= 0) {
            \DB::rollBack();
            return response()->json(['message'=> trans('custom.error_occured')],200);
        } else {
            \DB::commit();
            return response()->json(['result'=> config('define.result.success')],200);
        }
    }
    
}
