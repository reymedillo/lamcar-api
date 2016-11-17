<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Language
{
    public function handle($request, Closure $next)
    {
        if ($request->header('Accept-Language') AND array_key_exists($request->header('Accept-Language'), Config::get('languages'))) {
            App::setLocale($request->header('Accept-Language'));
        }
        else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App::setLocale(Config::get('app.fallback_locale'));
        }
        return $next($request);
    }
}

