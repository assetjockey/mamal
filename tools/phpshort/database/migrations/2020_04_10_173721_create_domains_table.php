<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainsTable extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
    {
		Schema::create('domains', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('name', 255)->index('name');
			$table->string('index_page', 255)->nullable();
			$table->string('not_found_page', 255)->nullable();
			$table->integer('user_id')->index('user_id');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
    {
		Schema::drop('domains');
	}
}
