<?php

use App\Enums\UserRole\Role;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowersController;
use App\Http\Controllers\Forum\ForumCommentController;
use App\Http\Controllers\Forum\ForumGroupController;
use App\Http\Controllers\Forum\ForumThreadController;
use App\Http\Controllers\Post\LikePostController;
use App\Http\Controllers\Post\PostCommentController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\product\HeartedProductController;
use App\Http\Controllers\Product\ManageProductController;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/logout', 'logout');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/resend-otp', 'resendOtp');
    Route::post('/reset-password', 'resetPassword')->middleware('jwt.auth');
    Route::get('/me', 'me')->middleware('jwt.auth');
    Route::get('/user', 'user');
    Route::post('/update-password', 'updatePassword')->middleware('jwt.auth');
    Route::post('/update-profile', 'updateProfile')->middleware('jwt.auth');
});

//manage product for brand, store and wholesaler
Route::group([
    'middleware' => [
        'jwt.auth',
        'check.role:' . implode(',', [Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value]), // Correctly implode into a string
        'check.product.owner'
    ]
], function () {
    Route::apiResource('product-manage', ManageProductController::class)->except(['create', 'edit']);

    Route::apiResource('post', PostController::class)->except(['create', 'edit']);
});



//manage follow
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('/follow', [FollowersController::class, 'follow']);
    Route::post('/unfollow', [FollowersController::class, 'unfollow']);
    Route::get('/get-followers-list', [FollowersController::class, 'getAllFollowers']);
    Route::get('/get-following-list', [FollowersController::class, 'getAllFollowing']);

    //post like
    Route::post('/tigger-like/{postId}', [LikePostController::class, 'tiggerLike']);
    Route::get('/get-likes-count/{postId}', [LikePostController::class, 'getLikesCount']);
    Route::get('/get-likes-by-post-id/{postId}', [LikePostController::class, 'getLikesByPostId']);

    //post comment
    Route::apiResource('post-comment', PostCommentController::class)->except(['create', 'edit','update', 'show']);

    //Forum group
    Route::apiResource('forum-group', ForumGroupController::class)->except(['create', 'edit']);

    //Forum threads
    Route::apiResource('forum-thread', ForumThreadController::class)->except(['create', 'edit']);

    //Forum comments
    Route::apiResource('forum-comment', ForumCommentController::class)->except(['create', 'edit','update', 'show']);

    //hearted product
    Route::apiResource('hearted-product',HeartedProductController::class)->except(['create', 'edit','update', 'show', 'destroy']);

    // Route::get('/test', function () {
    //     $user = auth()->user();
    //     return $user->following()->select('first_name','email','role')->get();
    // });
});
