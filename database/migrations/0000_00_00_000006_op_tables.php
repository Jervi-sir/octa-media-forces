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
        Schema::create('ops', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('full_name')->nullable();
            $table->string('password');
            $table->string('password_plain_text');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('wilaya_id')->constrained();
            $table->timestamps();
            $table->index('wilaya_id');
        });

        Schema::create('op_contact_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->foreignId('contact_platforms_id')->constrained();
            $table->string('link');
            $table->timestamps();
        });
        Schema::create('save_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->timestamps();

            $table->index(['op_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('op_shipping_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->string('ticket_code');
            $table->enum('status', ['pending', 'shipped', 'delivered']);
            $table->timestamps();
        });
        Schema::create('op_shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->string('full_name');
            $table->string('phone_number');
            $table->foreignId('wilaya_id')->constrained();
            $table->string('location')->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->timestamps();

            $table->index(['op_id', 'status']);
            $table->index('wilaya_id');
        });
        Schema::create('op_buyings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->enum('status', ['pending', 'paid', 'canceled', 'shipped', 'delivered']);
            $table->string('shipping_address')->nullable();
            $table->foreignId('op_shipping_address_id')->constrained();
            $table->string('ticket_id')->nullable();
            $table->timestamps();

            $table->index(['op_id', 'status']);
            $table->index('product_id');
            $table->index('op_shipping_address_id');
        });
        Schema::create('op_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->string('name');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->index('op_id');
        });
        Schema::create('op_collections_os', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_collection_id')->constrained();
            $table->foreignId('os_id')->constrained();
            $table->timestamps();

            $table->index('op_collection_id');
            $table->index('os_id');
        });

        Schema::create('op_friend_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_1_id')->constrained('ops');
            $table->foreignId('op_2_id')->constrained('ops');
            $table->foreignId('sender_id')->constrained('ops');
            $table->enum('status', ['pending', 'accepted', 'rejected']);
            $table->timestamps();
        });
        Schema::create('op_friends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_1_id')->constrained('ops');
            $table->foreignId('op_2_id')->constrained('ops');
            $table->foreignId('sender_id')->constrained('ops');
            $table->enum('status', ['active', 'blocked', 'removed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ops');
        Schema::dropIfExists('op_contact_lists');
        Schema::dropIfExists('save_posts');
        Schema::dropIfExists('op_buyings');
        Schema::dropIfExists('op_collections');
        Schema::dropIfExists('op_collections_os');
        Schema::dropIfExists('op_shipping_tickets');
        Schema::dropIfExists('op_shipping_addresses');
        Schema::dropIfExists('op_friend_requests');
        Schema::dropIfExists('op_friends');
    }
};
