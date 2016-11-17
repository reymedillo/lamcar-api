<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PushAccept extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:accept {arg1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accept car request.';

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
        $this->info("start push accept");

        $order_id   = $this->argument('arg1');
        $order     = \App\Order::find($order_id);

        if(!$order) {
            return $this->error('There is no order data.');
        }

        $user = \App\User::find($order->user_id);
        $message = \Lang::get('custom.push_accept', [], $user->language);
        $option = ['order_id' => $order_id];

        if ($user->device_type == "iPhone") {
            $message = \PushNotification::Message($message, [
                'content-available' => 1,
                'custom' => $option
            ]);
            $push = \PushNotification::app('ios_user');
        } elseif ($user->device_type == "android") {
            $message = \PushNotification::Message($message, $option);
            $push = \PushNotification::app('android');
        } else {
            return $this->error('Device type error');
        }
        $push->to($user->device_id)->send($message);

        $this->info("end push accept");
    }

}
