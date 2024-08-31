<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TagController;
use App\Jobs\DeleteOldSoftDeletedPost;
use App\Jobs\LogUserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\PostCondition;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login-user',[UserController::class,'login']);
Route::post('/register-user',[UserController::class,'Register']);
Route::post('/verify-user',[UserController::class,'verifyUser']);
Route::post('/resend-verification-code',[UserController::class,'resendVerificationCode']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('stats', [StateController::class, 'index']);
    Route::prefix('tags')->group(function(){
        Route::get('/',[TagController::class,'index']);
        Route::post('/',[TagController::class,'store']);
        Route::patch('{tag}',[TagController::class,'update']);
        Route::delete('{tag}',[TagController::class,'destroy']);
    });

    Route::prefix('posts')->group(function(){
        Route::get('view-deleted',[PostController::class,'viewDeleted']);
        Route::get('/me',[PostController::class,'getUserPosts']);
        Route::get('{post}',[PostController::class,'show']);
        Route::post('/',[PostController::class,'store']);
        Route::put('{post}',[PostController::class,'update']);
        Route::delete('{post}',[PostController::class,'destroy']);
        Route::put('{id}/restore', [PostController::class, 'restore']);
    });


});

Route::get('/test-fetch-random-user', function () {
    dispatch(new LogUserData());
    return 'Job dispatched!';
});
Route::get('/test-force-delete-old-posts', function () {
    dispatch(new DeleteOldSoftDeletedPost());
    return 'Job dispatched!';
});
