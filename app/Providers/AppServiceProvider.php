<?php

namespace App\Providers;

use App\Interfaces\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\AuthRepository;
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
