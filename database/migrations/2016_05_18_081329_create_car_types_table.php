<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_en');
            $table->string('name_ja');
            $table->float('base');
            $table->float('per_mile');
            $table->float('cancel');
            $table->tinyInteger('seat_num');
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
        Schema::drop('car_types');
    }
}
