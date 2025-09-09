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
    Schema::create('ogms', function (Blueprint $table) {
      $table->id();
      $table->string('displayed_id', 32)->unique();
      $table->foreignId('wilaya_id')->nullable()->constrained();
      $table->string('full_name', 200)->nullable();
      $table->string('username')->unique();
      $table->string('password');
      $table->string('password_plain_text');
      $table->string('phone_number')->nullable();
      $table->string('image')->nullable();
      $table->boolean('is_approved')->default(false);
      $table->timestamps();
      $table->index('wilaya_id');
    });

    Schema::create('ogm_notification_types', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('icon')->nullable();
      $table->string('title_en', 100)->nullable(); // Limit to 100 chars
      $table->string('title_ar', 100)->nullable();
      $table->string('title_fr', 100)->nullable();
      $table->text('content_en')->nullable();     // Keep text for flexibility
      $table->text('content_ar')->nullable();
      $table->text('content_fr')->nullable();
    });
    Schema::create('ogm_notifications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained();
      $table->foreignId('ogm_notification_type_id')->constrained();
      $table->text('content')->nullable();
      $table->boolean('is_opened')->default(false);
      $table->timestamps();

      $table->index(['ogm_id', 'is_opened']);
      $table->index('ogm_notification_type_id');
    });
    Schema::create('ogm_logs_types', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->timestamps();
    });
    Schema::create('ogm_logs_data', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained('ogms');
      $table->foreignId('ogm_logs_type_id')->constrained();
      $table->text('body');
      $table->timestamps();
    });
    Schema::create('ogm_payments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained('ogms');
      $table->string('payment_id');
      $table->enum('status', ['pending', 'completed', 'failed', 'refunded']);
      $table->timestamps();
    });

    /** ------------------------
     * Qualifications
     * ------------------------- */
    Schema::create('ogm_tutorials', function (Blueprint $table) {
      $table->id();
      $table->string('video_url');
      $table->string('title');
      $table->timestamp('posted_at');
      $table->timestamps();
    });
    Schema::create('ogm_operation_tests', function (Blueprint $table) {
      $table->id();
      $table->string('order_number');
      $table->string('name');
      $table->text('body');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ogm_tutorials');
    Schema::dropIfExists('ogm_operation_tests');
  }
};
