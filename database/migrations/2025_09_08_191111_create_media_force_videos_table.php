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
        Schema::create('media_force_videos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('media_force_id')->constrained()->cascadeOnDelete();

            // 1..11 per media-force
            $t->unsignedTinyInteger('slot_number'); // 1..11
            $t->string('title')->nullable();
            $t->text('description')->nullable();

            // file storage
            $t->string('file_path')->nullable();     // storage path
            $t->string('thumbnail_path')->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();

            // workflow
            $t->enum('status', ['draft', 'submitted', 'approved', 'changes_requested', 'rejected'])
                ->default('draft')->index();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('review_feedback')->nullable();

            $t->timestamps();

            $t->unique(['media_force_id', 'slot_number']);
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
