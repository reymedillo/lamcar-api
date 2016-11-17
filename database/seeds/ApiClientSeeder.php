<?php

use Illuminate\Database\Seeder;

class ApiClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('api_clients')->insert([
        	'name' 				=> 'web.hireapp.com',
        	'secret'			=> str_random(4),
        	'valid'				=> true
        ]);
    }
}
