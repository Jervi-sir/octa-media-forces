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
        Schema::create('os', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ogm_id')->constrained();
            $table->foreignId('wilaya_id')->constrained();
            $table->string('store_name');
            $table->string('image')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('map_link')->nullable();
            $table->text('bio')->nullable();

            $table->boolean('is_approved')->default(false);
            $table->string('is_blocked')->default(false);

            $table->timestamps();
            $table->index('ogm_id');
            $table->index('wilaya_id');
        });
        Schema::create('os_contact_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('os_id')->constrained();
            $table->foreignId('contact_platforms_id')->constrained();
            $table->string('link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('os');
        Schema::dropIfExists('os_contact_lists');
    }
};
