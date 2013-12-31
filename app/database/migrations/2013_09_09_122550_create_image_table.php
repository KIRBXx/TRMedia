<?php

use Illuminate\Database\Migrations\Migration;

class CreateImageTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('image_name');
            $table->string('title');
            $table->string('slug');
            $table->text('image_description');
            $table->string('category');
            $table->string('tags');
            $table->string('type');
            $table->boolean('approved')->default(1);
            $table->integer('is_featured')->nullable();
            $table->boolean('allow_download')->default(1);
            $table->integer('downloads')->default(0);
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