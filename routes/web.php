<?php

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



// Admin review
// Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/reviews', [AdminVideoReviewController::class, 'index'])->name('reviews.index');
//     Route::get('/reviews/{video}', [AdminVideoReviewController::class, 'show'])->name('reviews.show');
//     Route::post('/reviews/{video}/approve', [AdminVideoReviewController::class, 'approve'])->name('reviews.approve');
//     Route::post('/reviews/{video}/request-changes', [AdminVideoReviewController::class, 'requestChanges'])->name('reviews.request_changes');
//     Route::post('/reviews/{video}/reject', [AdminVideoReviewController::class, 'reject'])->name('reviews.reject');
// });


require __DIR__.'/web/media_forces.php';
require __DIR__.'/web/admin.php';

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
