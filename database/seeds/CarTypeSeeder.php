<?php

use Illuminate\Database\Seeder;

class CarTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('car_types')->insert([
            'name_en'               => 'Van',
            'name_ja'               => 'Van',
            'base'                  => '40.00',
            'per_mile'              => '5.00',
            'cancel'                => '5.00',
            'seat_num'              => '8',
            'valid'                 => 1
        ]);
        \DB::table('car_types')->insert([
            'name_en'               => 'Bus',
            'name_ja'               => 'Bus',
            'base'                  => '20.00',
            'per_mile'              => '8.00',
            'cancel'                => '8.00',
            'seat_num'              => '4',
            'valid'                 => 1
        ]); 
    }
}
