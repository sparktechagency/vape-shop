<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfSuspended
{
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check() && Auth::user()->isSuspended()) {

            $suspendedUntil = Auth::user()->suspended_until->format('d M, Y h:i A');


            return response()->json([
                'message' => 'Your account is suspended until ' . $suspendedUntil,
                'reason' => Auth::user()->suspend_reason,
                'suspended_until' => Auth::user()->suspended_until
            ], 403);
        }


        return $next($request);
    }
}
