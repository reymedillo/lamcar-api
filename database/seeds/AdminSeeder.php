<?php

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('admins')->insert([
        	'name' 			=> 'rei',
        	'password'		=> bcrypt('1234'),
        	'valid'			=> true
        ]);
    }
}
