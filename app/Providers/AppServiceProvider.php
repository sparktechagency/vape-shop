<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Interfaces\Products\ManageProductsInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Products\ManageProductsRepository;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

/**
 * @method static \Illuminate\Http\JsonResponse successResponse(mixed $data = null, string $message = 'Success', int $code = 200)
 * @method static \Illuminate\Http\JsonResponse errorResponse(string $message = 'Something went wrong', int $code = 400, mixed $errors = null)
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ManageProductsInterface::class, ManageProductsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('successResponse', function ($data = null, $message = 'Success', $code = 200) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'data' => $data,
            ], $code);
        });
        Response::macro('errorResponse', function ($message = 'Something went wrong', $code = 400, $errors = null) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'errors' => $errors,
            ], $code);
        });
    }
}
