<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Car;

class PushRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:request {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push Request to all cars.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Car $car)
    {
        parent::__construct();
        $this->car = $car;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("start push request");

        $order_id = $this->argument('arg1');
        $order = \App\Order::find($order_id);

        if(!$order) {
            return $this->error('There is no order data.');
        }

        \DB::beginTransaction();
        $order->pickup_location_en = \App\Order::checkLocationEN($order,'pickup');
        $order->dropoff_location_en = \App\Order::checkLocationEN($order,'dropoff');

        if($order->save()) {
            \DB::commit();
        } else {
            \DB::rollBack();
            return $this->error('There is error in saving location EN.');
        }

        $cars = $this->car->getAvailableCars(
            $order->pickup_latitude,
            $order->pickup_longitude,
            $order->car_type_id
        ); 

        if(count($cars) == 0) {
            return $this->error('There is no available car.');
        }

        $message = \Lang::get('custom.push_request', [], 'en');
        $option = ['order_id' => $order_id];
        $devices = [];
        foreach($cars as $car) {
            $devices[$car->device_type][] = \PushNotification::Device($car->device_id); 
        }

        foreach($devices as $device_type => $device){
            if ($device_type == "iPhone") {
                $message = \PushNotification::Message($message, [
                    'content-available' => 1,
                    'custom' => $option
                ]);
                $push = \PushNotification::app('ios_user');
            } elseif ($device_type == "android") {
                $message = \PushNotification::Message($message, $option);
                $push = \PushNotification::app('android');
            } else {
                $this->info("Device type error(Device type:".$device_type.")");
                continue;
            }
            $push->to(\PushNotification::DeviceCollection($device))->send($message);
        }

        $this->info("end push request");
    }
}
