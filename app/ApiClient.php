<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{

    protected $table = 'api_clients';
    
    /*
     * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [       
            'name', 'android', 'secret', 'valid', 'updated_at'   
    ];
    
    public function register($request){
    
        foreach($request->input() AS $k => $v){
 
               if($k == 'client_name' || $k == 'client_secret')
                   
                   $this->{str_replace('client_', '', $k)} = $v;
               
            if(in_array($k,$this->getFillable()))
             
                $this->{$k} = $v;
            
        }
            
        return $this->save();
         
    }
  
}
