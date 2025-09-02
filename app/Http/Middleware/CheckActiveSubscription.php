<?php

namespace App\Http\Middleware;

use App\Enums\UserRole\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $user = Auth::user();

        // if ($user && in_array($user->role, [Role::BRAND->value, Role::STORE->value, Role::WHOLESALER->value])) {

        //     if (!$user->hasActiveSubscription()) {

        //         Auth::logout();


        //         return response()->json([
        //             'ok' => false,
        //             'is_subscribed' => false,
        //             'message' => 'Your subscription has expired. Please renew your subscription.'
        //         ], 403); // 403 Forbidden status code
        //     }
        // }

        //subscription check disabled for now - to be enabled in future

        return $next($request);
    }
}
