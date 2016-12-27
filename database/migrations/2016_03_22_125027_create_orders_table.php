<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('contact')->nullable()->default(null);
            $table->string('pickup_location');
            $table->string('pickup_location_en')->nullable()->default(null);
            $table->string('pickup_latitude');
            $table->string('pickup_longitude');
            $table->string('pickup_location_detail')->nullable()->default(null);
            $table->string('dropoff_location');
            $table->string('dropoff_location_en')->nullable()->default(null);
            $table->string('dropoff_latitude');
            $table->string('dropoff_longitude');
            $table->float('distance');
            $table->integer('car_type_id');
            $table->float('fare');
            $table->integer('car_id')->nullable()->default(null);
            $table->integer('driver_id')->nullable()->default(null);
            $table->dateTime('pickup_scheduled_date')->nullable()->default(null);
            $table->dateTime('order_date');
            $table->dateTime('accept_date')->nullable()->default(null);
            $table->dateTime('arrive_date')->nullable()->default(null);
            $table->dateTime('pickup_date')->nullable()->default(null);
            $table->dateTime('dropoff_date')->nullable()->default(null);
            $table->dateTime('cancel_date')->nullable()->default(null);
            $table->tinyInteger('status');
            $table->boolean('valid')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('orders');
    }
}
