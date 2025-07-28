<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => App\Http\Middleware\JWTAuthMiddleware::class,
            'check.role' => App\Http\Middleware\CheckRole::class,
            'check.product.owner' => App\Http\Middleware\CheckProductOwner::class,
            'guest' => App\Http\Middleware\GuestMiddleware::class,
            'banned' => App\Http\Middleware\CheckIfBanned::class,
            'check.subscription' => App\Http\Middleware\CheckActiveSubscription::class,
            'is.suspended' => \App\Http\Middleware\CheckIfSuspended::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('subscriptions:expire')->everyMinute();
    })->create();
