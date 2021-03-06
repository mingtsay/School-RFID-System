<?php

use Illuminate\Database\Migrations\Migration;

class CreateActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity', function($table){
			$table->increments('aid')->unique();
			$table->text('activity_name');
			$table->text('activity_desc');
			$table->timestamp('activity_date')->nullable();
			$table->text('activity_type');
			$table->integer('nid');
			$table->text('activity_organize')->nullable();
			$table->text('activity_note')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity');
	}

}