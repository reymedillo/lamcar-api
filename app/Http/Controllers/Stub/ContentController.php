<?php

namespace App\Http\Controllers\Stub;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests;

class ContentController extends Controller
{
    protected function getHowtourl(Request $request)
    {   
        return response()->json(
            array(
                'url' => 'http://howtourl.com?lang=ja',
            ), 200 );
    }

    protected function getDisclaimer(Request $request)
    {   
        return response()->json(
            array(
               'contents' => 'testtesttesttesttesttest',
            ), 200 );
    }

}
