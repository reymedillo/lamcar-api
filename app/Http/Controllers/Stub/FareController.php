<?php

namespace App\Http\Controllers\Stub;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests;

class FareController extends Controller
{
    protected function getShow(Request $request)
    {
        return response()->json(
            array(
                'fare' => '13.50',
            ), 200 );
    }
}
