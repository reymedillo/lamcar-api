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
        	'number' 				=> 2,
        	'note'				=> 'car 2'
        ]);
    }
}
