<?php

use App\Http\Controllers\MediaForce\MediaAuthController;
use App\Http\Controllers\MediaForce\MediaDashboardController;
use App\Http\Controllers\MediaForce\MediaVideoController;

use Illuminate\Support\Facades\Route;

Route::prefix('media-forces')->group(function () {
  Route::get('auth',  [MediaAuthController::class, 'showLogin'])->name('media_forces.login');
  Route::post('login', [MediaAuthController::class, 'login'])->name('media_forces.login.submit');
  // Route::get('register', [MediaAuthController::class, 'showRegister'])->name('media_forces.register');
  Route::post('register', [MediaAuthController::class, 'register'])->name('media_forces.register.submit');

  Route::middleware(['user_type:MediaForce'])->group(function () {
    Route::post('logout', [MediaAuthController::class, 'logout'])->name('media_forces.logout');

    Route::get('dashboard', [MediaDashboardController::class, 'index'])->name('media_forces.dashboard');
    Route::get('videos', [MediaVideoController::class, 'index'])->name('media_forces.videos.index');
  });


  Route::prefix('videos')->middleware(['user_type:MediaForce'])->group(function () {
    Route::post('upload', [MediaVideoController::class, 'upload'])->name('media_forces.videos.upload');   // direct file upload
    Route::post('store',  [MediaVideoController::class, 'store'])->name('media_forces.videos.store');     // persist DB row
  });
});
