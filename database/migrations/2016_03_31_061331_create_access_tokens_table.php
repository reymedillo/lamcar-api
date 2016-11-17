<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('api_token');
            $table->dateTime('expired_date');
            $table->string('refresh_token');
            $table->dateTime('refresh_token_expired_date')->nullable()->default(null);
            $table->enum('role', array('admin', 'user', 'car'));
            $table->integer('account_id');
            $table->string('api_client_id');
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
        Schema::drop('access_tokens');
    }
}
