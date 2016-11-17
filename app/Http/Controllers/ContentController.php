<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

class ContentController extends Controller
{
    protected function getHowtourl(Request $request)
    {   
        return response()->json(['url' => env('WEB_URL').'/howto?lang='.\App::getLocale()], 200 );
    }

    protected function getDisclaimer(Request $request)
    {   
        return response()->json(['contents' => trans('privacy-policy.body')], 200);
    }

}
