<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('countries', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('number');
			$table->string('en');
			$table->string('ar');
			$table->string('fr');
			$table->timestamps();
		});
		Schema::create('wilayas', function (Blueprint $table) {
			$table->id();
			$table->foreignId('country_id');
			$table->string('name');
			$table->tinyInteger('number');
			$table->string('number_text');
			$table->string('en');
			$table->string('ar');
			$table->string('fr');
			$table->timestamps();

			$table->index('number');
		});
		Schema::create('contact_platforms', function (Blueprint $table) {
			$table->id();
			$table->string('name')->unique();
			$table->string('code_name')->unique();
			$table->string('icon_url')->nullable();
			$table->string('icon_svg')->nullable();
			$table->string('description')->nullable();
			$table->timestamps();
		});
		Schema::create('notification_tokens', function (Blueprint $table) {
			$table->id();
			$table->enum('user_type', ['admin', 'ot', 'ogm', 'os', 'op']);
			$table->unsignedBigInteger('actor_id');
			$table->string('token');
			$table->string('device_id');
			$table->string('device_type');
			$table->timestamps();

			$table->index(['actor_id', 'user_type']);
			$table->index('token');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('countries');
	}
};
