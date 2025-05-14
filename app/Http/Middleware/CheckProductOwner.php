<?php

namespace App\Http\Middleware;

use App\Models\ManageProduct;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckProductOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $product = ManageProduct::select('user_id')->find($request->route('product_manage'));
        $post = Post::select('user_id')->find($request->route('post'));
        
        if ($post && $post->user_id !== auth()->id()) {
            // Clear cache for the old role if it exists
            $oldRole = session('user_role');
            if ($oldRole && $oldRole !== auth()->user()->role) {
                Cache::forget("posts_{$oldRole}_page_1"); // Clear old role's cache
            }

            // Update session with the new role
            session(['user_role' => auth()->user()->role]);

            return response()->errorResponse(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this post. Please login with "' . $post->role . ' Role" account'
            );
        }

        if ($product && $product->user_id !== auth()->id()) {
            // Clear cache for the old role if it exists
            $oldRole = session('user_role');
            if ($oldRole && $oldRole !== auth()->user()->role) {
                Cache::forget("products_{$oldRole}_page_1"); // Clear old role's cache
            }

            // Update session with the new role
            session(['user_role' => auth()->user()->role]);

            return response()->errorResponse(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this product. Please login with "' . $product->role . ' Role" account'
            );
        }

        return $next($request);
    }
}
