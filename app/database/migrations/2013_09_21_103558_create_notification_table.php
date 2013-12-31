<?php

use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification', function ($t) {
            $t->increments('id');
            $t->string('type');
            $t->string('user_id');
            $t->string('from_id');
            $t->string('on_id')->nullable();
            $t->smallInteger('is_read')->default(0);
            $t->timestamps();
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