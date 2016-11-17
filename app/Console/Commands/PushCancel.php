<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PushCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:cancel {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push Cancel to the car.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("start push cancel");

        $order_id = $this->argument('arg1');
        $order = \App\Order::find($order_id);

        if(!$order || is_null($order->car_id)) {
            return $this->error('There is no order data or not accepted.');
        }

        $car  = \App\Car::find($order->car_id);
        $message = \Lang::get('custom.push_cancel', [], 'en');
        $option = ['order_id' => $order_id];

        if ($car->device_type == "iPhone") {
            $message = \PushNotification::Message($message, [
                'content-available' => 1,
                'custom' => $option
            ]);
            $push = \PushNotification::app('ios_car');
        } elseif ($car->device_type == "android") {
            $message = \PushNotification::Message($message, $option);
            $push = \PushNotification::app('android');
        } else {
            return $this->error('Device type error');
        }
        $push->to($car->device_id)->send($message);

        $this->info("end push cancel");
    }
}
