<?php

use Illuminate\Database\Migrations\Migration;

class AddSourceToImages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
    Schema::table('images', function($table) {
      $table->string('source', 200);
    });

		//
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
