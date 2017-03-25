<?php

use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('cars')->insert([
            'car_type_id'           => 1,
            'number'                => '232',
            'note'                  => '#232 , plate# ACS456',
            'valid'                 => 1
        ]);
        \DB::table('cars')->insert([
            'car_type_id'           => 2,
            'number'                => '234',
            'note'                  => '#234 , plate# ACS326',
            'valid'                 => 1
        ]);
    }
}
