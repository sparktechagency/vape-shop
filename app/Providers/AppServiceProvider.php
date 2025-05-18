<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Interfaces\FollowersInterface;
use App\Interfaces\Forum\ForumGroupInterface;
use App\Interfaces\Forum\ForumThreadInterface;
use App\Interfaces\Post\PostCommentInterface;
use App\Interfaces\Post\PostInterface;
use App\Interfaces\Post\PostLikeInterface;
use App\Interfaces\Products\ManageProductsInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\FollowersRepository;
use App\Repositories\Forum\ForumGroupRepository;
use App\Repositories\Forum\ForumThreadRepository;
use App\Repositories\Post\PostCommentRepository;
use App\Repositories\Post\PostLikeRepository;
use App\Repositories\Post\PostRepository;
use App\Repositories\Products\ManageProductsRepository;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
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
        $this->app->bind(PostCommentInterface::class, PostCommentRepository::class);
        $this->app->bind(ForumGroupInterface::class, ForumGroupRepository::class);
        $this->app->bind(ForumThreadInterface::class, ForumThreadRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
