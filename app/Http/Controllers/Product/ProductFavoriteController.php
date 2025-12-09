<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductFavoriteController extends Controller
{
    public function toggleProductFavorite(Request $request)
    {
        $user = auth()->user();

        $validator = \Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'type' => 'required|string|in:store,brand,wholesaler',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 400, $validator->errors());
        }

        $productId = $request->product_id;
        $type = $request->type; // 'store', 'brand', 'wholesaler'

        $modelClass = match ($type) {
            'store' => \App\Models\StoreProduct::class,
            'brand' => \App\Models\ManageProduct::class,
            'wholesaler' => \App\Models\WholesalerProduct::class,
            default => null,
        };

        if (!$modelClass) return response()->json(['error' => 'Invalid type'], 400);

        $existing = \App\Models\ProductFavourite::where('user_id', $user->id)
            ->where('favouritable_id', $productId)
            ->where('favouritable_type', $modelClass)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->success(null,'Removed from favorites');
        } else {
            \App\Models\ProductFavourite::create([
                'user_id' => $user->id,
                'favouritable_id' => $productId,
                'favouritable_type' => $modelClass
            ]);
            return response()->success(null, 'Added to favorites');
        }
    }


    public function getUserFavorites(Request $request)
    {
        $user = auth()->user();

        $favorites = $user->productFavourites()->with('favouritable')->get()->map(function ($fav) {
            return [
                'id' => $fav->favouritable->id,
                'type' => class_basename($fav->favouritable_type),
                'details' => $fav->favouritable,
            ];
        });

        if($favorites->isEmpty()) {
            return response()->error('No favorites found', 404);
        }

        return response()->success($favorites, 'User favorites retrieved successfully');
    }
}
