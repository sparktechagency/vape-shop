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
use App\Models\ForumGroup;
use App\Models\ForumThread;
use App\Models\Order;
use App\Observers\OrderObserver;
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

        Order::observe(OrderObserver::class);
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
