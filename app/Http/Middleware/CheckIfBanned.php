<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->banned_at) {

            $bannedUser = Auth::user();
            $banReason = $bannedUser->ban_reason;
            $bannedAt = $bannedUser->banned_at->toIso8601String();
            Auth::logout();

             return response()->json([
                'ok' => false,
                'error_code' => 'ACCOUNT_BANNED',
                'message' => 'Your account has been suspended. Please contact support.',
                'details' => [
                    'reason' => $banReason ?? 'No reason provided',
                    'banned_at' => $bannedAt,
                ]
            ], 403);
        }

        return $next($request);
    }
}
