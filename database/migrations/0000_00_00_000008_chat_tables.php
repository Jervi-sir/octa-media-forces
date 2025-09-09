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
        /*
        |--------------------------------------------------------------------------
        | OP OP
        |--------------------------------------------------------------------------
        */
        Schema::create('op_op_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_1_id')->constrained('ops');
            $table->foreignId('op_2_id')->constrained('ops');
            $table->boolean('is_deleted_by_op_1')->default(false);
            $table->boolean('is_deleted_by_op_2')->default(false);
            $table->timestamp('deleted_by_op_1_date')->nullable();
            $table->timestamp('deleted_by_op_2_date')->nullable();
            $table->timestamps();
            
            $table->index(['op_1_id', 'op_2_id']);
            $table->index('op_1_id');
            $table->index('op_2_id');
        });
        Schema::create('op_op_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_op_chat_id')->constrained();
            $table->foreignId('sender_id')->constrained('ops');
            $table->enum('sender_type', ['op_1', 'op_2']);
            $table->boolean('is_seen_by_receiver')->default(false);
            $table->enum('message_type', ['text', 'image', 'file', 'product', 'store']);
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'seen', 'failed', 'deleted'])->default('pending');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['op_op_chat_id', 'sent_at']);
            $table->index('is_seen_by_receiver');
            $table->index(['sender_id', 'sender_type']);

        });
        /*
        |--------------------------------------------------------------------------
        | OP OS
        |--------------------------------------------------------------------------
        */
        Schema::create('op_os_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->foreignId('os_id')->constrained();
            $table->boolean('is_deleted_by_op')->default(false);
            $table->boolean('is_deleted_by_os')->default(false);
            $table->timestamp('deleted_by_op_date')->nullable();
            $table->timestamp('deleted_by_os_date')->nullable();
            $table->timestamps();

            $table->index(['op_id', 'os_id']);
            $table->index('op_id');
            $table->index('os_id');
        });
        Schema::create('op_os_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_os_chat_id')->constrained();
            $table->unsignedBigInteger('sender_id');
            $table->enum('sender_type', ['op', 'os']);
            $table->boolean('is_seen_by_receiver')->default(false);
            $table->enum('message_type', ['text', 'image', 'file', 'product', 'store']);
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'seen', 'failed', 'deleted'])->default('pending');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['op_os_chat_id', 'sent_at']);
            $table->index('is_seen_by_receiver');
            $table->index(['sender_id', 'sender_type']);
        });

        /*
        |--------------------------------------------------------------------------
        | OP OT
        |--------------------------------------------------------------------------
        */
        Schema::create('op_ot_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_id')->constrained();
            $table->foreignId('ot_id')->constrained();
            $table->boolean('is_deleted_by_op')->default(false);
            $table->boolean('is_deleted_by_ot')->default(false);
            $table->timestamp('deleted_by_op_date')->nullable();
            $table->timestamp('deleted_by_os_date')->nullable();
            $table->timestamps();

            $table->index(['op_id', 'ot_id']);
            $table->index('op_id');
            $table->index('ot_id');
        });
        Schema::create('op_ot_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('op_ot_chat_id')->constrained();
            $table->unsignedBigInteger('sender_id');
            $table->enum('sender_type', ['op', 'ot']);
            $table->boolean('is_seen_by_receiver')->default(false);
            $table->enum('message_type', ['text', 'image', 'file', 'product', 'store']);
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'seen', 'failed', 'deleted'])->default('pending');
            $table->timestamp('sent_at');
            $table->timestamps();
            
            $table->index(['op_ot_chat_id', 'sent_at']);
            $table->index('is_seen_by_receiver');
            $table->index(['sender_id', 'sender_type']);
        });
        /*
        |--------------------------------------------------------------------------
        | OGM OT
        |--------------------------------------------------------------------------
        */
        Schema::create('ogm_ot_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ogm_id')->constrained();
            $table->foreignId('ot_id')->constrained();
            $table->boolean('is_deleted_by_ogm')->default(false);
            $table->boolean('is_deleted_by_ot')->default(false);
            $table->timestamp('deleted_by_ogm_date')->nullable();
            $table->timestamp('deleted_by_ot_date')->nullable();
            $table->timestamps();

            $table->index(['ogm_id', 'ot_id']);
            $table->index('ogm_id');
            $table->index('ot_id');
        });
        Schema::create('ogm_ot_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ogm_ot_chat_id')->constrained();
            $table->unsignedBigInteger('sender_id');
            $table->enum('sender_type', ['ogm', 'ot']);
            $table->boolean('is_seen_by_receiver')->default(false);
            $table->enum('message_type', ['text', 'image', 'file', 'product', 'store']);
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'delivered', 'seen', 'failed', 'deleted'])->default('pending');
            $table->timestamp('sent_at');
            $table->timestamps();
            
            $table->index(['ogm_ot_chat_id', 'sent_at']);
            $table->index('is_seen_by_receiver');
            $table->index(['sender_id', 'sender_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('op_op_chat_messages');
        Schema::dropIfExists('op_os_chat_messages');
        Schema::dropIfExists('op_ot_chat_messages');
        Schema::dropIfExists('ogm_ot_chat_messages');
        Schema::dropIfExists('op_op_chats');
        Schema::dropIfExists('op_os_chats');
        Schema::dropIfExists('op_ot_chats');
        Schema::dropIfExists('ogm_ot_chats');
    }
};
