<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v2_9\OGM\AuthController;
use App\Http\Controllers\Api\v2_9\OGM\ChatController;
use App\Http\Controllers\Api\v2_9\OGM\HelperController;
use App\Http\Controllers\Api\v2_9\OGM\M5MeController;
use App\Http\Controllers\Api\v2_9\OGM\M3TagController;
use App\Http\Controllers\Api\v2_9\OGM\MediaController;
use App\Http\Controllers\Api\v2_9\OGM\StoreController;
use App\Http\Controllers\Api\v2_9\OGM\M3DraftController;
use App\Http\Controllers\Api\v2_9\OGM\M4ChatController;
use App\Http\Controllers\Api\v2_9\OGM\M2OrderController;
use App\Http\Controllers\Api\v2_9\OGM\M3StatsController;
use App\Http\Controllers\Api\v2_9\OGM\M3PrePostController;
use App\Http\Controllers\Api\v2_9\OGM\M1TutorialController;
use App\Http\Controllers\Api\v2_9\OGM\M3PublishedController;
use App\Http\Controllers\Api\v2_9\OGM\NotificationController;
use App\Http\Controllers\Api\v2_9\OGM\StoreContactController;
use App\Http\Controllers\Api\v2_9\OGM\QualificationController;
use App\Http\Controllers\Api\v2_9\OGM\QualificationCommentController;

Route::prefix('v2.9/ogm')->group(function () {
    // Auth
    Route::get('username/confirm-availability', [AuthController::class, 'confirmUsernameAvailability']);            // [done]
    Route::post('register', [AuthController::class, 'register']);                                                   // [done]
    Route::post('login', [AuthController::class, 'login']);                                                         // [done]
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'user_type:Ogm']);        // [done]
    Route::post('logout/all', [AuthController::class, 'logoutAll'])->middleware(['auth:sanctum', 'user_type:Ogm']); // [done]
    Route::get('validate-auth-token', [AuthController::class, 'validateAuthToken'])->middleware(['auth:sanctum', 'user_type:Ogm']); // [done]
    Route::get('quick-get-my-stores', [AuthController::class, 'quickGetMyStores'])->middleware(['auth:sanctum', 'user_type:Ogm']); // [done]
    
    // Notifications
    Route::prefix('push-notifications')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function() {
        Route::post('/', [NotificationController::class, 'subscribeToPushNotifications']);
        Route::patch('/{deviceId}', [NotificationController::class, 'updatePushNotifications']);
    });

    // Media
    Route::post('media-upload/image', [MediaController::class, 'uploadImage']);

    // helper
    Route::prefix('helpers')->group(function () {
        Route::get('bootstrap', [HelperController::class, 'listData']);
        Route::get('get-wilayas', [HelperController::class, 'getWilayas']);
        Route::get('genders-categories-sizes', [HelperController::class, 'getGendersCategoriesSizes']);
        Route::get('trending-tags', [HelperController::class, 'getTrendingTags']);
        Route::get('search-tags', [HelperController::class, 'searchTags']);
        Route::post('submit-new-tag', [HelperController::class, 'submitNewTag']);
        Route::get('list-platforms', [HelperController::class, 'listPlatforms']);
    });
    /*
    |--------------------------------------------------------------------------
    | Qualifications
    |--------------------------------------------------------------------------
    */
    Route::prefix('qualification')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::get('status', [QualificationController::class, 'status']);
        Route::post('update-progress', [QualificationController::class, 'updateProgress']);
        
        Route::prefix('to-read')->group(function () {
            Route::get('{type}', [QualificationController::class, 'toRead']);
            Route::get('{type}/comments', [QualificationCommentController::class, 'listComments']);
            Route::post('{type}/comments', [QualificationCommentController::class, 'storeComment']);
            Route::post('{type}/comments/{comment_id}/reply', [QualificationCommentController::class, 'replyComment']);
            Route::post('{type}/comments/{comment_id}/like', [QualificationCommentController::class, 'likeComment']);
            Route::post('{type}/like', [QualificationCommentController::class, 'likeArticle']);
        });

        Route::post('operation-1', [QualificationController::class, 'createStore']);
        Route::get('operation-1', [QualificationController::class, 'getStore']);
        Route::get('operation-2', [QualificationController::class, 'getOperation2']);
        Route::post('operation-3', [QualificationController::class, 'saveOperation3']);
        Route::get('operation-3', [QualificationController::class, 'getOperation3']);
        Route::post('operation-4', [QualificationController::class, 'saveOperation4']);
        Route::get('operation-4', [QualificationController::class, 'getOperation4']);
        
        Route::get('claim-your-store', [QualificationController::class, 'claimYourStore']);
        Route::get('test-ogm-eligibility', [QualificationController::class, 'testOgmEligibility']);

    });

       // Chat
     Route::prefix('chat')->group(function () {
        Route::get('ot', [ChatController::class, 'listOtChats']);
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::get('/', [NotificationController::class, 'listNotifications']);                      // []
        Route::get('/{id}/mark-opened', [M1TutorialController::class, 'markAsOpened']);             // []
    });

    /*
  |--------------------------------------------------------------------------
  | M1
  |--------------------------------------------------------------------------
  */
    Route::prefix('m1')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::get('tutorials', [M1TutorialController::class, 'listTutorials']);            // []
    });

    /*
  |--------------------------------------------------------------------------
  | M2
  |--------------------------------------------------------------------------
  */
    Route::prefix('m2')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::get('orders', [M2OrderController::class, 'listOrders']);
    });

    /*
  |--------------------------------------------------------------------------
  | M3
  |--------------------------------------------------------------------------
  */
    Route::prefix('m3')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        // Route::post('upload-image', [M3ImageController::class, 'uploadImage']);
        // Route::get('stats', [M3StatsController::class, 'getStats']);
        Route::get('count', [M3StatsController::class, 'getCounts']);

        Route::prefix('drafts')->group(function () {
            Route::post('/', [M3DraftController::class, 'create']);             // [done]
            Route::get('/', [M3DraftController::class, 'list']);                // [done]
            Route::delete('/', [M3DraftController::class, 'delete']);           // [done]
        });

        Route::prefix('pre-posts')->group(function () {
            Route::get('/', [M3PrePostController::class, 'list']);                      // [done]
            Route::post('/', [M3PrePostController::class, 'create']);                   // [done]
        });

        Route::get('product/{product_id}', [M3PrePostController::class, 'show']);
        Route::put('product/{product_id}', [M3PrePostController::class, 'update']);
        Route::post('product/{product_id}/refresh', [M3PrePostController::class, 'refresh']);
        Route::delete('product/{product_id}', [M3PrePostController::class, 'delete']);

        Route::prefix('tags')->group(function () {
            Route::get('/', [M3TagController::class, 'list']);
            Route::post('/', [M3TagController::class, 'create']);
        });

        Route::prefix('published')->group(function () {
            Route::get('/', [M3PublishedController::class, 'list']);
            Route::put('{product_id}', [M3PublishedController::class, 'update']);
        });

    });


    /*
  |--------------------------------------------------------------------------
  | M4
  |--------------------------------------------------------------------------
  */
    Route::prefix('m4')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::prefix('chats')->group(function () {
            Route::get('/', [M4ChatController::class, 'listChats']);
            Route::get('{chat_id}', [M4ChatController::class, 'showChat']);
        });
    });

    /*
  |--------------------------------------------------------------------------
  | M5
  |--------------------------------------------------------------------------
  */
    Route::prefix('me')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::get('/', [M5MeController::class, 'profile']);
        Route::get('settings', [M5MeController::class, 'getSettings']);
        Route::put('settings', [M5MeController::class, 'updateSettings']);

        Route::get('password', [M5MeController::class, 'getPassword']);
        Route::put('password', [M5MeController::class, 'updatePassword']);
        Route::get('phone-number', [M5MeController::class, 'getPhoneNumber']);
        Route::put('phone-number', [M5MeController::class, 'updatePhoneNumber']);
        Route::get('username', [M5MeController::class, 'getUsername']);
        Route::put('username', [M5MeController::class, 'updateUsername']);
    });

    Route::prefix('stores')->middleware(['auth:sanctum', 'user_type:Ogm'])->group(function () {
        Route::get('/', [StoreController::class, 'listStores']);
        Route::post('/', [StoreController::class, 'createNewStores']);
        Route::get('{os_id}', [StoreController::class, 'showStore']);
        Route::put('{os_id}', [StoreController::class, 'updateStore']);
        Route::get('{os_id}/contacts', [StoreContactController::class, 'listContacts']);
        Route::post('{os_id}/contacts', [StoreContactController::class, 'addContact']);
        Route::put('{os_id}/contacts/{contact_id}', [StoreContactController::class, 'updateContact']);
        Route::delete('{os_id}/contacts/{contact_id}', [StoreContactController::class, 'deleteContact']);
    });
});
