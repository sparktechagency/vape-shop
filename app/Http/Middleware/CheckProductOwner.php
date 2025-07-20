<?php

namespace App\Http\Middleware;

use App\Enums\UserRole\Role;
use App\Models\ManageProduct;
use App\Models\Post;
use App\Models\StoreProduct;
use App\Models\WholesalerProduct;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProductOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        if (auth()->user()->role === Role::STORE) {
            $product = StoreProduct::find($request->route('product_manage'));
        } elseif (auth()->user()->role === Role::WHOLESALER) {
            $product = WholesalerProduct::find($request->route('product_manage'));
        } else {
            $product = ManageProduct::find($request->route('product_manage'));
        }

        $post = Post::find($request->route('post'));

        if ($post && $post->user_id !== auth()->id()) {

            $roleLabel = $post->role ? $post->role->label() : 'correct';

            return response()->error(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this post. Please login with the "' . $roleLabel . '" account.'
            );
        }

        if ($product && $product->user_id !== auth()->id()) {

            $roleLabel = $product->role ? $product->role->label() : 'correct';

            return response()->error(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this product. Please login with the "' . $roleLabel . '" account.'
            );
        }

        return $next($request);
    }
}
