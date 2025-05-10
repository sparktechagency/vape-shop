<?php

namespace App\Http\Middleware;

use App\Models\ManageProduct;
use Closure;
use Illuminate\Http\Request;
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
        $product = ManageProduct::find($request->route('product_manage'));
        // dd($product);
        if($product && $product->user_id !== auth()->id()) {
            return response()->errorResponse(
                'You are not authorized to perform this action.',
                403,
                'You are not the owner of this product. Please login with "'.$product->role . ' Role" account'
            );
        }
        return $next($request);
    }
}
