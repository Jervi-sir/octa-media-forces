<?php

use App\Http\Controllers\MediaForce\MediaAuthController;
use App\Http\Controllers\MediaForce\MediaDashboardController;
use App\Http\Controllers\MediaForce\MediaVideoController;

use Illuminate\Support\Facades\Route;

Route::prefix('media-forces')->group(function () {
  Route::get('login',  [MediaAuthController::class, 'showLogin'])->name('media.login');
  Route::post('login', [MediaAuthController::class, 'login'])->name('media.login.submit');
  Route::get('register', [MediaAuthController::class, 'showRegister'])->name('media.register');
  Route::post('register', [MediaAuthController::class, 'register'])->name('media.register.submit');

  Route::middleware('auth:media_forces')->group(function () {
    Route::post('logout', [MediaAuthController::class, 'logout'])->name('media.logout');

    Route::get('dashboard', [MediaDashboardController::class, 'index'])->name('media.dashboard');
    Route::get('videos', [MediaVideoController::class, 'index'])->name('media.videos.index');
  });


  Route::prefix('videos')->middleware('auth:media_forces')->group(function () {
    Route::post('upload', [MediaVideoController::class, 'upload'])->name('media.videos.upload');   // direct file upload
    Route::post('store',  [MediaVideoController::class, 'store'])->name('media.videos.store');     // persist DB row
  });
});
