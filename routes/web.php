<?php

use App\Http\Controllers\MediaAuthController;
use App\Http\Controllers\MediaDashboardController;
use App\Http\Controllers\MediaVideoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});


Route::get('/media/login',  [MediaAuthController::class, 'showLogin'])->name('media.login');
Route::post('/media/login', [MediaAuthController::class, 'login'])->name('media.login.submit');
Route::get('/media/register', [MediaAuthController::class, 'showRegister'])->name('media.register');
Route::post('/media/register', [MediaAuthController::class, 'register'])->name('media.register.submit');
Route::post('/media/logout', [MediaAuthController::class, 'logout'])->name('media.logout');

Route::prefix('media')->middleware('auth:media_forces')->group(function () {
    Route::get('/dashboard', [MediaDashboardController::class, 'index'])->name('media.dashboard');
    Route::get('/videos', [MediaVideoController::class, 'index'])->name('media.videos.index');
    // Route::get('/videos/{slot}', [MediaVideoController::class, 'show'])->name('videos.show');
    // Route::post('/videos/{slot}', [MediaVideoController::class, 'store'])->name('videos.store'); // upload/update
    // Route::post('/videos/{slot}/submit', [MediaVideoController::class, 'submit'])->name('videos.submit');
});


Route::prefix('media')->middleware('auth:media_forces')->group(function () {
    Route::post('/videos/upload', [MediaVideoController::class, 'upload'])->name('media.videos.upload');   // direct file upload
    Route::post('/videos/store',  [MediaVideoController::class, 'store'])->name('media.videos.store');     // persist DB row
});


// Admin review
// Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/reviews', [AdminVideoReviewController::class, 'index'])->name('reviews.index');
//     Route::get('/reviews/{video}', [AdminVideoReviewController::class, 'show'])->name('reviews.show');
//     Route::post('/reviews/{video}/approve', [AdminVideoReviewController::class, 'approve'])->name('reviews.approve');
//     Route::post('/reviews/{video}/request-changes', [AdminVideoReviewController::class, 'requestChanges'])->name('reviews.request_changes');
//     Route::post('/reviews/{video}/reject', [AdminVideoReviewController::class, 'reject'])->name('reviews.reject');
// });



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
