<?php

use App\Enums\UserRole\Role;
use App\Http\Controllers\AboutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\FollowersController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\CountryRegionController;
use App\Http\Controllers\Post\LikePostController;
use App\Http\Controllers\Admin\ArticlesController;
use App\Http\Controllers\Product\ReviewController;
use App\Http\Controllers\Product\TrendingProducts;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Forum\ForumGroupController;
use App\Http\Controllers\MostFollowersAdsController;
use App\Http\Controllers\Post\PostCommentController;
use App\Http\Controllers\Forum\ForumThreadController;
use App\Http\Controllers\Forum\ForumCommentController;
use App\Http\Controllers\Product\HomeProductController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Product\ManageProductController;
use App\Http\Controllers\Product\HeartedProductController;
use App\Http\Controllers\Admin\AdApprovalsManageController;
use App\Http\Controllers\Admin\AdPricingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\AdminMetricController;
use App\Http\Controllers\B2bConnectionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\B2bPricingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConnectedLocationController;
use App\Http\Controllers\FeaturedAdRequestController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\Forum\ForumGroupMemberController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\Product\TrendingAdProductController;
use App\Http\Controllers\SubscriptionController;
use App\Models\Subscription;


Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/logout', 'logout');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/resend-otp', 'resendOtp');
    Route::post('/reset-password', 'resetPassword')->middleware('jwt.auth', 'banned');
    Route::get('/me', 'me')->middleware('jwt.auth', 'banned');
    Route::get('/profile/{id}', 'profile')->middleware('guest');
    Route::post('/update-password', 'updatePassword')->middleware('jwt.auth', 'banned', 'is.suspended');
    Route::post('/update-profile', 'updateProfile')->middleware('jwt.auth', 'banned', 'is.suspended');
});

//** admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['jwt.auth', 'banned', 'check.role:' . Role::ADMIN->value]], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);


    Route::get('/manage-users', [UserManagementController::class, 'manageUsers']);
    Route::get('/user/{id}', [UserManagementController::class, 'getUserById']);
    Route::get('/get-all-users', [UserManagementController::class, 'getAllUsers']);
    //=============Band and unban user============
    Route::put('/ban-user/{id}', [UserManagementController::class, 'banUser']);
    Route::put('/unban-user/{id}', [UserManagementController::class, 'unbanUser']);
    Route::get('/get-banned-users', [UserManagementController::class, 'getBannedUsers']);
    //============suspend user============
    Route::get('/users/suspended', [UserManagementController::class, 'suspendedUsers']);
    Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend']);
    Route::post('/users/{user}/unsuspend', [UserManagementController::class, 'unsuspend']);

    //============Send notification to user============
    Route::post('/users/{user}/notify', [UserManagementController::class, 'sendNotification']);

    //delete user
    Route::delete('/delete-user/{id}', [UserManagementController::class, 'deleteUser']);

    //slider
    Route::apiResource('slider', SliderController::class)->except(['create', 'edit']);
    //article
    Route::get('/get-all-articles', [ArticlesController::class, 'getAllArticles']);
    //delete article
    Route::delete('/delete/article/{id}', [ArticlesController::class, 'deleteArticle']);
    //update article

    //advertisement
    Route::get('/get-all-ad-requests', [AdApprovalsManageController::class, 'getAllAdRequests']);
    Route::get('/get-ad-request-by-id/{id}', [AdApprovalsManageController::class, 'getAdRequestById']);
    Route::put('/update-ad-request-status/{id}', [AdApprovalsManageController::class, 'updateAdRequestStatus']);

    //transaction history
    Route::get('/transaction-history', [TransactionController::class, 'index']);

    // ============= Subscription Management =============
    //update subscription plan
    Route::get('/plans', [AdminSubscriptionController::class, 'getPlans']);
    Route::put('/update-subscription-plan/{id}', [AdminSubscriptionController::class, 'updatePlan']);

    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index']);
    Route::get('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'show']);
    Route::put('/subscriptions/{subscription}/status', [AdminSubscriptionController::class, 'updateInvoiceStatus']);

    //page create
    Route::get('/pages/{type}', [PageController::class, 'getPageContent'])->withoutMiddleware(['jwt.auth', 'banned', 'check.role:' . Role::ADMIN->value])->middleware('guest');
    Route::post('/pages/update', [PageController::class, 'updateOrCreatePage']);

    //category
    Route::apiResource('category',CategoryController::class)->except(['create', 'edit']);
    //get all products by category
    Route::get('/category/{category}/products', [CategoryController::class, 'getProductsByCategory']);
    Route::apiResource('region', RegionController::class)->except(['create', 'edit']);

    //ad pricing
    Route::get('/ad-pricings', [AdPricingController::class, 'index'])->withoutMiddleware(['check.role:' . Role::ADMIN->value]);
    Route::post('/ad-pricings', [AdPricingController::class, 'saveOrUpdate']);
    Route::delete('/ad-pricings/{id}', [AdPricingController::class, 'destroy']);

    //country
    Route::apiResource('country',CountryController::class)->except(['create', 'edit']);

    Route::post('/interactions/update', [AdminMetricController::class, 'storeOrUpdate']);
    Route::get('/interactions/get', [AdminMetricController::class, 'getMetric']);
    Route::get('/interactions/list', [AdminMetricController::class, 'getAllAdjustments']);
});


//admin, brand, store and wholesaler middleware
Route::group(['middleware' => ['jwt.auth', 'banned', 'is.suspended', 'check.role:' . implode(',', [Role::ADMIN->value, Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value])]], function () {
    //update payment gateway credentials
    Route::post('/update-payment-gateway-credentials', [PaymentGatewayController::class, 'updatePaymentGateway']);
    //get payment gateway credentials
    Route::get('/get-payment-gateway-credentials', [PaymentGatewayController::class, 'getPaymentGatewayCredentials']);
});

//manage product for brand, store and wholesaler
Route::group([
    'middleware' => [
        'jwt.auth',
        'banned',
        'check.role:' . implode(',', [Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value]), // Correctly implode into a string
        'check.product.owner',
        'check.subscription',
        'is.suspended'
    ]
], function () {
    Route::apiResource('product-manage', ManageProductController::class)->except(['create', 'edit']);
});

//middleware for brand store and wholesaler
Route::group([
    'middleware' => [
        'jwt.auth',
        'banned',
        'check.role:' . implode(',', [Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value])
    ],
    'check.subscription',
    'is.suspended'
], function () {

    //b2b connection
    Route::post('/b2b/request/{provider}', [B2bConnectionController::class, 'sendRequest']);
    Route::put('/b2b/request/{connection}/update', [B2bConnectionController::class, 'updateRequest']);
    Route::get('/b2b/requests/incoming', [B2bConnectionController::class, 'listIncoming']);

    //b2b pricing
    Route::post('/b2b/product-pricing', [B2bPricingController::class, 'storeOrUpdate']);
    Route::get('/b2b/get-product-list', [B2bPricingController::class, 'getB2bProducts']);
    Route::delete('/b2b/product-pricing/{id}', [B2bPricingController::class, 'destroy']);
    Route::get('/b2b/seller-product-list/{seller}', [B2bPricingController::class, 'listProductsOfSeller']);

    //b2b checkout
    Route::post('/b2b/checkout', [CheckoutController::class, 'placeOrder']);
    Route::get('/b2b/orders/{checkout:checkout_group_id}/cancel', [CheckoutController::class, 'cancelOrder']);
});




//** Store Order routes
Route::group(['middleware' => ['jwt.auth', 'banned', 'check.role:' . Role::STORE->value, 'check.subscription']], function () {
    Route::get('/orders', [\App\Http\Controllers\OrderController::class, 'index']);
    Route::get('/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show']);
    Route::put('/orders/{order}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->middleware('is.suspended');

    //connected Location
    Route::get('/branches', [ConnectedLocationController::class, 'getMyBranches']);
    Route::get('/users/{user}/active-branches', [ConnectedLocationController::class, 'getActiveBranchesForUser']);
    Route::post('connected-location', [ConnectedLocationController::class, 'storeBranchRequest']);
    Route::delete('/branches/{branch}/cancel', [ConnectedLocationController::class, 'cancelBranchRequest']);

    //**=====================New connected location logic====================**//

    Route::post('/connected-location/add', [ConnectedLocationController::class, 'sendConnectionRequest']);
    Route::get('/connected-location/get', [ConnectedLocationController::class, 'getConnectedLocations']);
    Route::get('/incoming-connected-location/requests', [ConnectedLocationController::class, 'getIncomingConnectionRequests']);
    Route::post('/connected-location/respond/{id}', [ConnectedLocationController::class, 'respondToRequest']);
    Route::delete('/connected-location/remove/{id}', [ConnectedLocationController::class, 'removeConnection']);
});




//** manage follow
Route::group(['middleware' => ['jwt.auth', 'banned', 'check.subscription', 'is.suspended']], function () {
    Route::post('/follow', [FollowersController::class, 'follow']);
    Route::post('/unfollow', [FollowersController::class, 'unfollow']);
    Route::get('/get-followers-list', [FollowersController::class, 'getAllFollowers']);
    Route::get('/get-following-list', [FollowersController::class, 'getAllFollowing']);

    //post like
    Route::post('/tigger-like/{postId}', [LikePostController::class, 'tiggerLike']);
    Route::get('/get-likes-count/{postId}', [LikePostController::class, 'getLikesCount']);
    Route::get('/get-likes-by-post-id/{postId}', [LikePostController::class, 'getLikesByPostId']);

    //feed
    Route::get('/feed', [FeedController::class, 'feed']);

    //post comment
    Route::apiResource('post-comment', PostCommentController::class)->except(['create', 'edit', 'update', 'show']);
    //hearted product


    //message routes
    Route::post('send-message', [MessageController::class, 'sendMessage']);
    Route::get('get-message', [MessageController::class, 'getMessage']);
    Route::get('search-new-user', [MessageController::class, 'searchNewUser']);
    Route::get('chat-list', [MessageController::class, 'chatList']);
    Route::post('mark-as-read/{senderId}', [MessageController::class, 'markAsRead']);

    //order request
    Route::post('/order-request', [CheckoutController::class, 'orderRequest'])->middleware('check.role:' . Role::MEMBER->value);
    Route::post('/checkout/{checkout:checkout_group_id}/cancel', [CheckoutController::class, 'cancelOrderRequest'])
     ->middleware('check.role:' . Role::MEMBER->value);
    Route::get('/checkouts', [CheckoutController::class, 'index']);
    Route::get('/checkouts/{checkout:checkout_group_id}', [CheckoutController::class, 'show']);

    //inbox routes
    Route::post('/inbox/send-message', [InboxController::class, 'sendMessage']);
    Route::get('/inbox/{userId}', [InboxController::class, 'getInboxByUserId']);
    //delete a message
    Route::delete('/inbox/delete-message/{id}', [InboxController::class, 'deleteMessage']);
});

Route::apiResource('hearted-product', HeartedProductController::class)->middleware('jwt.auth')->except(['create', 'edit', 'update', 'show', 'destroy']);

//reviews product
Route::apiResource('product-review', ReviewController::class)->except(['create', 'edit', 'update', 'show']);
Route::post('/reviews/{review}/toggle-like', [ReviewController::class, 'toggleReviewLike']);
//most rated reviews
Route::get('/most-rated-reviews', [ReviewController::class, 'mostRatedReviews']);

//user latest reviews
Route::get('/user-latest-reviews', [ReviewController::class, 'userLatestReviews']);

Route::get('/my-latest-reviews', [ReviewController::class, 'myLatestReviews']);

//==================forum routes=======================
//Forum group
Route::apiResource('forum-group', ForumGroupController::class)->except(['create', 'edit'])->parameter('forum-group', 'group');

//Forum threads
Route::apiResource('forum-thread', ForumThreadController::class)->except(['create', 'edit']);
//like and unlike forum threads
Route::post('/forum-thread/{thread}/like', [ForumThreadController::class, 'likeUnlikeThread']);

//Forum comments
Route::apiResource('forum-comment', ForumCommentController::class)->except(['create', 'edit', 'update', 'show']);
//like and unlike forum comments
Route::post('/forum-comment/{id}/like', [ForumCommentController::class, 'likeUnlikeComment']);


Route::prefix('forum-groups/{group}')->group(function () {
    // Member Actions
    Route::post('/request-to-join', [ForumGroupMemberController::class, 'requestToJoin']);
    Route::post('/leave', [ForumGroupMemberController::class, 'leaveGroup']);

    // Owner/Admin Actions
    Route::get('/join-requests', [ForumGroupMemberController::class, 'listJoinRequests']);
    Route::post('/requests/{user}/approve', [ForumGroupMemberController::class, 'approveRequest']);
    Route::post('/requests/{user}/reject', [ForumGroupMemberController::class, 'rejectRequest']);
    Route::post('/members/{user}/remove', [ForumGroupMemberController::class, 'removeMember']);
     Route::get('/members', [ForumGroupMemberController::class, 'listApprovedMembers']);
});


//==================forum routes=======================
//post and article routes
Route::get('/posts/trending', [PostController::class, 'getTrendingPosts']);
Route::apiResource('post', PostController::class)->except(['create', 'edit']);
//get post by user id
Route::get('/get-posts-by-user-id/{userId}', [PostController::class, 'getPostsByUserId']);
//About page
Route::apiResource('about', AboutController::class)->except(['create', 'edit', 'update', 'show', 'destroy']);



//home product
Route::group(['middleware' => 'guest'], function () {
    //homecontroller
    Route::controller(HomeController::class)->group(function () {
        Route::get('/get-all-store-brand-wholesaler', 'getAllStoreBrandWholesaler');
        Route::get('/get/{userId}/products', 'getProductsByRoleId');
        Route::get('/stores-by-location', 'searchLocations');
        Route::get('/search', 'search');
        // Route::get('find-nearby-locations', 'searchLocations');
    });




    Route::get('get-all-products', [HomeProductController::class, 'index']);
    Route::get('get-product/{id}', [HomeProductController::class, 'show']);

    //get trending products
    Route::get('most-hearted-products', [TrendingProducts::class, 'mostHeartedProducts']);
    //get ad requests products
    Route::get('ad-request-trending-products', [TrendingProducts::class, 'adRequestsProducts']);
    //ad requests most followers
    Route::get('ad-request-most-follower', [MostFollowersAdsController::class, 'adRequestMostFollower']);
    //get most followers brand
    Route::get('most-followers-brand', [TrendingProducts::class, 'mostFollowersBrand']);

    //get slider in home page
    Route::get('slider', [SliderController::class, 'index']);

    //favorite brands and stores
    Route::apiResource('favourite', FavouriteController::class)->except(['create', 'edit', 'update', 'show', 'destroy']);
});



//Country And Region
Route::get('/get-all-countries', [CountryRegionController::class, 'getAllCountries']);
Route::get('/get-regions-by-country/{countryId}', [CountryRegionController::class, 'getRegionsByCountryId']);

Route::get('/get-all-categories', [HomeProductController::class, 'getAllCategories']);


//Ad trending products routes
Route::group(['middleware' => ['jwt.auth', 'check.role:' . Role::BRAND->value, 'check.subscription', 'is.suspended']], function () {
    Route::apiResource('trending-ad-product', TrendingAdProductController::class)->except(['create', 'edit']);
    Route::apiResource('most-followers-ad', MostFollowersAdsController::class)->except(['create', 'edit', 'show', 'update',]);
    Route::apiResource('featured-ad-request',FeaturedAdRequestController::class)->except(['create', 'edit']);
});


//Subscription routes
Route::get('/subscriptions/plans', [SubscriptionController::class, 'getSubscriptionsPlan']);
Route::middleware(['jwt.auth', 'is.suspended'])->group(function () {
    Route::post('/subscriptions/request', [SubscriptionController::class, 'processSubscriptionRequest']);
});



//notification routes
Route::middleware('jwt.auth')->prefix('notifications')->as('notifications.')->group(function () {

    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
    Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});
