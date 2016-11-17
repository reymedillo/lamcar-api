<?php
namespace App;


class Helpers
{
    
    public function password_validators($validators){
         
        $validators::extend('must1CapLetter', function($attribute, $value)
        {
            return preg_match('/^(?=.*[A-Z]).+$/', $value);
        });
         
        $validators::extend('must1SmallLetter', function($attribute, $value)
        {
            return preg_match('/^(?=.*[a-z]).+$/', $value);
        });
         
        $validators::extend('must1number', function($attribute, $value)
        {
            return preg_match('/^(?=.*\d).+$/', $value);
        });
         
        $validators::extend('must1special', function($attribute, $value)
        {
            return preg_match('/^(?=.*[_\W]).+$/', $value);
        });
         
        return $validators;
         
    }
    
}