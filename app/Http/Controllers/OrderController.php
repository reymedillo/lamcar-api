<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Order;

class OrderController extends Controller
{
    
    public function getIndex(Request $request)
    {
        $orders = Order::getOrders($request);

        $pagination = clone($orders);
        $pagination = $pagination->toArray();
        unset($pagination['data']);

        $orders = $orders->getCollection()->all();

        return response()->json([
            'orders' => $orders,
            'pagination' => $pagination
        ],200);
        
    }

    public function getShow($order_id) {

        $refines['id'] = $order_id;
        $order = Order::setFilter($refines)->firstOrFail();

        return response()->json([
            'order'=>$order
        ],200);
        
    }

    public function getSendpush($order_id) {
        
        exec("php ".base_path("artisan")." push:request order_id  > /dev/null 2>&1 &");

        return response()->json([
            'result' => config('define.result.success'), 
        ], 200);
    }
    
    public function deleteOrder($order_id) {
        
        $refines['id'] = $order_id;
        $order = Order::setFilter($refines)->firstOrFail;
        $order->valid = 0;
        $order->save();
        
        return response()->json([
            'result' => config('define.result.success')
        ], 200);
        
    }
}
