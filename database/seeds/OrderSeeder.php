<?php

use Illuminate\Database\Seeder;


class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('orders')->insert([
            'user_id'               => 1,
            'name'                  => 'rei',
            'contact'               => '263-1424',
            'pickup_location'               => 'gagfa',
            'pickup_latitude'               => '10.56',
            'pickup_longitude'              => '89.56',
            'pickup_location_detail'        => 'beside sykes mabolo',
            'dropoff_location'              => 'sm',
            'dropoff_latitude'              => '56.89',
            'dropoff_longitude'             => '96.32',
            'distance'                      => '12.3',
            'pickup_scheduled_date'         => '2016-05-12',
            'fare'                          => '250.8',
            'car_id'                        => 1,
            'order_date'                    => \Carbon\Carbon::now(),
            'accept_date'                   => \Carbon\Carbon::now(),
            'status'                        => 2,
            'valid'                         => 1,
            'created_at'                    => \Carbon\Carbon::now()
        ]);
    }
}
