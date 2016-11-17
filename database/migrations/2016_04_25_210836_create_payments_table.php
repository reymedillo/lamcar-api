<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->enum('type', array('authorize', 'capture','void'))->default('authorize');
            $table->string('token')->nullable()->default(null);
            $table->dateTime('expired_date')->nullable()->default(null);
            $table->float('amount');
            $table->dateTime('payment_date')->nullable()->default(null);
            $table->string('transaction_id', 20)->nullable()->default(null);
            $table->tinyInteger('transaction_response_code')->nullable()->default(null);
            $table->text('api_response')->nullable();
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
        Schema::drop('payments');
    }
}
