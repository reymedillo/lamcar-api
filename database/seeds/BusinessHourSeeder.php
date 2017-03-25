<?php

use Illuminate\Database\Seeder;

class BusinessHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('business_hours')->insert([
            'time_zone'         => 'Asia/Manila',
            'open_time'         => '05:00:00',
            'close_time'        => '12:00:00',
            'valid'             => 1
        ]);
    }
}
