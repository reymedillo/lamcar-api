<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('car_type_id');
            $table->string('number');
            $table->text('note');
            $table->integer('driver_id')->nullable()->default(null);
            $table->string('device_id');
            $table->string('device_type');
            $table->string('latitude');
            $table->string('longitude');
            $table->dateTime('location_update_date');
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
        Schema::drop('cars');
    }
}
