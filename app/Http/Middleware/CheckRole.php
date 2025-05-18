<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$role): Response
    {
        $user = $request->user();
        if(!$user || !in_array($user->role, $role)) {
            return response()->error(
                'Unauthorized',
                403,
                null,
                'You do not have permission to access this resource.'
            );
        }
        return $next($request);
    }
}
