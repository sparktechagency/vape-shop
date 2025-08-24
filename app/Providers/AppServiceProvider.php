<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Interfaces\FollowersInterface;
use App\Interfaces\Forum\ForumGroupInterface;
use App\Interfaces\Forum\ForumThreadInterface;
use App\Interfaces\Front\HomeInterface;
use App\Interfaces\PaymentGatewayInterface;
use App\Interfaces\Post\PostInterface;
use App\Interfaces\Post\PostLikeInterface;
use App\Interfaces\Products\HeartedProductsInterface;
use App\Interfaces\Products\HomeProductInterface;
use App\Interfaces\Products\ManageProductsInterface;
use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Follower;
use App\Models\ForumGroup;
use App\Models\ForumThread;
use App\Models\MostFollowerAd;
use App\Models\Order;
use App\Models\Post;
use App\Models\Review;
use App\Models\Message;
use App\Models\User;
use App\Models\ManageProduct;
use App\Models\Slider;
use App\Models\Category;
use App\Observers\FollowerObserver;
use App\Observers\ForumGroupObserver;
use App\Observers\ForumThreadObserver;
use App\Observers\MessageObserver;
use App\Observers\MostFollowerAdObserver;
use App\Observers\OrderObserver;
use App\Observers\PostObserver;
use App\Observers\ReviewObserver;
use App\Observers\UserObserver;
use App\Observers\ManageProductObserver;
use App\Observers\SliderObserver;
use App\Observers\CategoryObserver;
use App\Policies\ForumGroupPolicy;
use App\Policies\ForumThreadPolicy;
use App\Policies\OrderPolicy;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\FollowersRepository;
use App\Repositories\Forum\ForumGroupRepository;
use App\Repositories\Forum\ForumThreadRepository;
use App\Repositories\Front\HomeRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\Post\PostLikeRepository;
use App\Repositories\Post\PostRepository;
use App\Repositories\Products\HeartedProductsRepository;
use App\Repositories\Products\HomeProductRepository;
use App\Repositories\Products\ManageProductsRepository;
use App\Services\Gateways\AuthorizeNetService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{

     protected $policies = [
        Order::class => OrderPolicy::class,
        ForumGroup::class => ForumGroupPolicy::class,
        ForumThread::class => ForumThreadPolicy::class,
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ManageProductsInterface::class, ManageProductsRepository::class);
        $this->app->bind(FollowersInterface::class, FollowersRepository::class);
        $this->app->bind(PostInterface::class, PostRepository::class);
        $this->app->bind(PostLikeInterface::class, PostLikeRepository::class);
        $this->app->bind(ForumGroupInterface::class, ForumGroupRepository::class);
        $this->app->bind(ForumThreadInterface::class, ForumThreadRepository::class);
        $this->app->bind(HeartedProductsInterface::class, HeartedProductsRepository::class);
        $this->app->bind(HomeInterface::class, HomeRepository::class);
        $this->app->bind(HomeProductInterface::class, HomeProductRepository::class);


        //payment services
        $this->app->bind(PaymentGatewayInterface::class, AuthorizeNetService::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Observers
        Order::observe(OrderObserver::class);
        User::observe(UserObserver::class);
        ManageProduct::observe(ManageProductObserver::class);
        Slider::observe(SliderObserver::class);
        Category::observe(CategoryObserver::class);

        // New observers for cache management
        ForumGroup::observe(ForumGroupObserver::class);
        ForumThread::observe(ForumThreadObserver::class);
        Post::observe(PostObserver::class);
        Review::observe(ReviewObserver::class);
        Message::observe(MessageObserver::class);
        MostFollowerAd::observe(MostFollowerAdObserver::class);
        Follower::observe(FollowerObserver::class);

        /**
         * @method static \Illuminate\Http\JsonResponse success(mixed $data = null, string $message = 'Success', int $code = 200)
         **/
        Response::macro('success', function ($data = null, $message = 'Success', $code = 200) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'data' => $data,
            ], $code);
        });

        /** @method static \Illuminate\Http\JsonResponse error(string $message = 'Something went wrong', int $code = 400, mixed $errors = null)
         */
        Response::macro('error', function ($message = 'Something went wrong', $code = 400, $errors = null) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'errors' => $errors,
            ], $code);
        });


    }
}
