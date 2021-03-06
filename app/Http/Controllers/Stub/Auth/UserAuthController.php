<?php

namespace App\Http\Controllers\Stub\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;

class UserAuthController extends Controller
{

    protected function postRefresh($user_id, Request $request)
    {
        $time = new Carbon(Carbon::now());
        return response()->json(
            array(
                'access_token' => str_random(75),
                'expired_date' => $time->addHour(1)->format('Y-m-d H:i:s'),
            ), 200 );
    }

}
