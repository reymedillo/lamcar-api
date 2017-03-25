<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(ApiClientSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(CarSeeder::class);
        $this->call(CarTypeSeeder::class);
        $this->call(DriverSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(BusinessHourSeeder::class);
    }
}
