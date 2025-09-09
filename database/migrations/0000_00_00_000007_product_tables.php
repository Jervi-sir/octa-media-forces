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
    Schema::create('statuses', function (Blueprint $table) {
      $table->id();
      $table->string('name', 50); // e.g., "pre-post", "published", "expired"
      $table->timestamps();
      $table->index('name');
    });

    Schema::create('categories', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('en');
      $table->string('ar');
      $table->string('fr');
      $table->timestamps();
    });

    Schema::create('sizes', function (Blueprint $table) {
      $table->id();
      $table->string('name', 50);
      $table->foreignId('category_id')->constrained()->onDelete('cascade');
      $table->timestamps();
      $table->index('name');
    });

    Schema::create('genders', function (Blueprint $table) {
      $table->id();
      $table->string('name', 50); // e.g., "male", "female", "unisex", "kids"
      $table->timestamps();
      $table->index('name');
    });

    Schema::create('tag_categories', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->timestamps();
    });
    Schema::create('tags', function (Blueprint $table) {
      $table->id();
      $table->foreignId('tag_category_id')->nullable()->constrained();
      $table->string('name');
      $table->string('en')->nullable();
      $table->string('ar')->nullable();
      $table->string('fr')->nullable();
      $table->timestamps();
      $table->index('name');
    });
    Schema::create('tag_networks', function (Blueprint $table) {
      $table->id();
      $table->foreignId('tag_id')->constrained();
      $table->timestamps();
      $table->index('tag_id');
    });
    Schema::create('drafts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('ogm_id')->constrained();
      $table->foreignId('os_id')->nullable()->constrained();
      $table->string('image_url');
      $table->timestamps();
      $table->index('ogm_id');
    });
    Schema::create('products', function (Blueprint $table) {
      $table->id();
      $table->foreignId('os_id')->constrained();
      $table->json('images');
      $table->decimal('price', 12, 2)->nullable();
      $table->decimal('discount', 8, 2)->nullable();
      $table->timestamp('discount_end_date')->nullable();
      $table->text('description')->nullable();
      $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
      $table->foreignId('status_id')->constrained();
      $table->timestamp('posted_at')->nullable();
      $table->timestamp('disappear_at')->nullable();
      $table->timestamps();

      $table->index(['os_id', 'status_id']);
      $table->index('category_id');
      $table->index(['status_id', 'posted_at']);
      $table->index('posted_at');
      $table->index('price');
      $table->index('discount_end_date');
    });
    Schema::create('product_genders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('product_id')->constrained()->onDelete('cascade');
      $table->foreignId('gender_id')->constrained('genders')->onDelete('cascade');
      $table->timestamps();
      $table->index(['product_id', 'gender_id']);
    });
    Schema::create('product_sizes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('product_id')->constrained()->onDelete('cascade');
      $table->foreignId('size_id')->constrained('sizes')->onDelete('cascade');
      $table->timestamps();
      $table->index(['product_id', 'size_id']);
    });
    Schema::create('product_tags', function (Blueprint $table) {
      $table->id();
      $table->foreignId('product_id')->constrained()->onDelete('cascade');
      $table->foreignId('tag_id')->constrained()->onDelete('cascade');
      $table->timestamps();
      $table->index(['product_id', 'tag_id']);
      $table->index('tag_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('product_tags');
    Schema::dropIfExists('product_sizes');
    Schema::dropIfExists('product_genders');
    Schema::dropIfExists('products');
    Schema::dropIfExists('drafts');
    Schema::dropIfExists('tag_networks');
    Schema::dropIfExists('tag_categories');
    Schema::dropIfExists('tags');
    Schema::dropIfExists('genders');
    Schema::dropIfExists('sizes');
    Schema::dropIfExists('categories');
    Schema::dropIfExists('statuses');
  }
};
