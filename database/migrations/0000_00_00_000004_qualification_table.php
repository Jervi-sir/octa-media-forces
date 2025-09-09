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
    Schema::create('ogm_qualification_progress', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('ogm_id')->unique();
      $table->foreign('ogm_id')->references('id')->on('ogms')->onDelete('cascade');
      $table->enum('status', ['pending', 'rejected', 'in-review', 'accepted'])->default('pending');
      $table->json('progress')->nullable();
      /*
                type QualificationCardProps = {
                progress: {
                    type?: 'start' | 'pending' | 'completed' | 'failed' | 'error',
                    test_score?: string,
                    progress?: number,
                };
                };
            */
      $table->timestamps();
    });
    Schema::create('qualification_operation3s', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained();
      $table->json('images')->nullable();
      $table->timestamps();
    });
    Schema::create('qualification_operation4s', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained();
      $table->string('question_1')->nullable();
      $table->string('question_2')->nullable();
      $table->string('question_3')->nullable();
      $table->string('question_4')->nullable();
      $table->timestamps();
    });
    Schema::create('qualification_reads', function (Blueprint $table) {
      $table->id();
      $table->string('type')->nullable();
      $table->string('title')->nullable();
      $table->longText('content')->nullable();
      $table->integer('likes')->default(0)->after('content');
      $table->timestamps();
    });
    Schema::create('qualification_comments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('qualification_read_id')->constrained()->onDelete('cascade');
      $table->foreignId('ogm_id')->constrained('ogms')->onDelete('cascade');
      $table->text('content');
      $table->foreignId('parent_id')->nullable()->constrained('qualification_comments')->onDelete('cascade');
      $table->integer('likes')->default(0);
      $table->timestamps();
    });
    Schema::create('qualification_likes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained('ogms')->onDelete('cascade');
      $table->morphs('likeable');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ogms');
    Schema::dropIfExists('ogm_notification_types');
    Schema::dropIfExists('ogm_logs_types');
    Schema::dropIfExists('ogm_logs_data');
    Schema::dropIfExists('ogm_payments');
    Schema::dropIfExists('ogm_notifications');
    Schema::dropIfExists('ogm_qualification_progress');
    Schema::dropIfExists('qualification_operation3s');
    Schema::dropIfExists('qualification_operation4s');
    Schema::dropIfExists('qualification_reads');
    Schema::dropIfExists('qualification_comments');
    Schema::dropIfExists('qualification_likes');
  }
};
