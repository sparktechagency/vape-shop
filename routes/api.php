<?php

use App\Enums\UserRole\Role;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CountryRegionController;
use App\Http\Controllers\FollowersController;
use App\Http\Controllers\Forum\ForumCommentController;
use App\Http\Controllers\Forum\ForumGroupController;
use App\Http\Controllers\Forum\ForumThreadController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Post\LikePostController;
use App\Http\Controllers\Post\PostCommentController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\product\HeartedProductController;
use App\Http\Controllers\Product\HomeProductController;
use App\Http\Controllers\Product\ManageProductController;
use App\Http\Controllers\Product\ReviewController;
use App\Http\Controllers\Product\TrendingProducts;
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

//admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['jwt.auth', 'check.role:' . Role::ADMIN->value]], function () {
    Route::get('/manage-users', [UserManagementController::class, 'manageUsers']);
    Route::get('/user/{id}', [UserManagementController::class, 'getUserById']);
    Route::get('/get-all-users', [UserManagementController::class, 'getAllUsers']);

    //slider
    Route::apiResource('slider', SliderController::class)->except(['create', 'edit']);
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
    Route::apiResource('post-comment', PostCommentController::class)->except(['create', 'edit', 'update', 'show']);
    //hearted product
    Route::apiResource('hearted-product', HeartedProductController::class)->except(['create', 'edit', 'update', 'show', 'destroy']);

    //reviews product
    Route::apiResource('product-review', ReviewController::class)->except(['create', 'edit', 'update', 'show']);
});

//forum routes
//Forum group
Route::apiResource('forum-group', ForumGroupController::class)->except(['create', 'edit']);

//Forum threads
Route::apiResource('forum-thread', ForumThreadController::class)->except(['create', 'edit']);

//Forum comments
Route::apiResource('forum-comment', ForumCommentController::class)->except(['create', 'edit', 'update', 'show']);



//home product
Route::group(['middleware' => 'guest'], function () {
    //homecontroller
    Route::controller(HomeController::class)->group(function () {
        Route::get('/get-all-store-brand-wholesaler', 'getAllStoreBrandWholesaler');
        Route::get('/get/{userId}/products', 'getProductsByRoleId');
    });




    Route::get('get-all-products', [HomeProductController::class, 'index']);
    Route::get('get-product/{id}', [HomeProductController::class, 'show']);

    //get trending products
    Route::get('most-hearted-products', [TrendingProducts::class, 'mostHeartedProducts']);
    //get most followers brand
    Route::get('most-followers-brand', [TrendingProducts::class, 'mostFollowersBrand']);

    //get slider in home page
    Route::get('slider', [SliderController::class, 'index']);
});



//Country And Region
Route::get('/get-all-countries', [CountryRegionController::class, 'getAllCountries']);
Route::get('/get-regions-by-country/{countryId}', [CountryRegionController::class, 'getRegionsByCountryId']);
