<?php

namespace App\Http\Middleware;

use App\Enums\UserRole\Role;
use App\Models\ManageProduct;
use App\Models\Post;
use App\Models\StoreProduct;
use App\Models\WholesalerProduct;
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
        // dd(auth()->user()->role);
        if (auth()->user()->role === Role::STORE->value) {
            $product = StoreProduct::select('user_id')->find($request->route('product_manage'));
        } elseif (auth()->user()->role === Role::WHOLESALER->value) {
            $product = WholesalerProduct::select('user_id')->find($request->route('product_manage'));
        } else {
            $product = ManageProduct::select('user_id')->find($request->route('product_manage'));
        }
        $post = Post::select('user_id')->find($request->route('post'));

        if ($post && $post->user_id !== auth()->id()) {


            // Update session with the new role
            session(['user_role' => auth()->user()->role]);

            return response()->error(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this post. Please login with "' . $post->role . ' Role" account'
            );
        }

        if ($product && $product->user_id !== auth()->id()) {
            

            // Update session with the new role
            session(['user_role' => auth()->user()->role]);

            return response()->error(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this product. Please login with "' . $product->role . ' Role" account'
            );
        }

        return $next($request);
    }
}
