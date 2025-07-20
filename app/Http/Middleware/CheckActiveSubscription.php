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
        $user = Auth::user();

        if ($user && in_array($user->role, [Role::BRAND, Role::STORE, Role::WHOLESALER])) {

            if (!$user->hasActiveSubscription()) {

                Auth::logout();


                return response()->json([
                    'ok' => false,
                    'is_subscribed' => false,
                    'message' => 'Your subscription has expired. Please renew your subscription to continue using the service.',
                ], 403); // 403 Forbidden status code
            }
        }

        return $next($request);
    }
}
