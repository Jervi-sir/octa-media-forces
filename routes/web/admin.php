<?php

use App\Http\Controllers\Admin\MediaForceController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
  Route::prefix('media-forces')->middleware('auth:admin')->group(function () {
    Route::get('/', [MediaForceController::class, 'index'])->name('admin.media_forces.index');
    Route::get('show/{mediaForce}', [MediaForceController::class, 'show'])->name('admin.media_forces.show');
    Route::patch('videos/{mediaForceVideo}', [MediaForceController::class, 'update'])->name('admin.media_forces.videos.update');

    Route::get('videos/{mediaForceVideo}/stream', [MediaForceController::class, 'stream'])->name('admin.media_forces.videos.stream');

  });
});
