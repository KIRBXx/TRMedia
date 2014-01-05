<?php

use Illuminate\Database\Migrations\Migration;

class AddVerifiedToUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
    Schema::table('users', function($table) {
      $table->boolean('is_verified');
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
