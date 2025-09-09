<?php

use App\Http\Controllers\Api\v2_9\Admin\QualificationController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'\api\v2_9\ogm.php';

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('test', [TestController::class, 'test']);

Route::get('admin/test', [QualificationController::class, 'submitReviewOgmQualification']);
Route::get('test/push-notifications', [PushNotificationController::class, 'sendToCurrentUser']);
