<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->bigInteger('fbid')->nullable();
            $table->string('username', 255);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->string('fullname');
            $table->date('dob');
            $table->string('gender')->default('male');
            $table->string('avatar')->default('user');
            $table->string('country')->nullable();
            $table->string('about_me')->nullable();
            $table->string('blogurl')->nullable();
            $table->string('fb_link')->nullable();
            $table->string('tw_link')->nullable();
            $table->integer('is_featured')->nullable();
            $table->string('permission')->nullable();
            $table->string('confirmed')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}