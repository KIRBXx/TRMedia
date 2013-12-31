<?php

use Illuminate\Database\Migrations\Migration;

class CreateReportTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report', function ($table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->string('report');
            $table->string('type');
            $table->string('description');
            $table->integer('solved')->default('0');
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