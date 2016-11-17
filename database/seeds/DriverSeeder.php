<?php

use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('drivers')->insert([
        	'login_id' 			=> 'juan',
        	'password'			=> bcrypt(1234),
        	'name'				=> 'Juan Cruz'
        ]);

        \DB::table('drivers')->insert([
        	'login_id' 			=> 'miguel',
        	'password'			=> bcrypt(1234),
        	'name'				=> 'Miguel Alvarez'
        ]);

        \DB::table('drivers')->insert([
        	'login_id' 			=> 'julio',
        	'password'			=> bcrypt(1234),
        	'name'				=> 'Julio Ruiz'
        ]);

        \DB::table('drivers')->insert([
        	'login_id' 			=> 'alicia',
        	'password'			=> bcrypt(1234),
        	'name'				=> 'Alicia Vasquez'
        ]);
    }
}
