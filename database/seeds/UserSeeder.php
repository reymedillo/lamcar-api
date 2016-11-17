<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->insert([
        	'device_id' 				=> 'asfaa3345',
        	'device_type'			=> 'ios',
        	'customer_profile_id'				=> '406698',
        	'customer_payment_profile_id'				=> '386698',
        	'valid' 						=> true,
        	'created_at' => \Carbon\Carbon::now()
        ]);
    }
}
