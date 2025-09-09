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
    Schema::create('media_forces', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('email')->unique();
      $table->string('password');
      $table->string('password_plain_text')->nullable();
      $table->rememberToken();
      $table->timestamps();
    });

    Schema::create('media_force_videos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('media_force_id')->constrained()->cascadeOnDelete();

      // 1..11 per media-force
      $table->unsignedTinyInteger('slot_number'); // 1..11
      $table->string('title')->nullable();
      $table->text('description')->nullable();

      // file storage
      $table->string('file_path')->nullable();     // storage path
      $table->string('thumbnail_path')->nullable();
      $table->unsignedInteger('duration_seconds')->nullable();

      // workflow
      $table->enum('status', ['draft', 'submitted', 'approved', 'changes_requested', 'rejected'])
        ->default('draft')->index();
      $table->timestamp('submitted_at')->nullable();
      $table->timestamp('reviewed_at')->nullable();
      $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
      $table->text('review_feedback')->nullable();

      $table->timestamps();

      $table->unique(['media_force_id', 'slot_number']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('media_force_videos');
  }
};
