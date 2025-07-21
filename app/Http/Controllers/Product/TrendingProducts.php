<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\ManageProduct;
use App\Models\TrendingProducts as ModelsTrendingProducts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrendingProducts extends Controller
{
    //most hearted products

    public function mostHeartedProducts(Request $request)
    {
        $regionId = request('region_id');
        $categoryId = request('category_id');

        $products = ManageProduct::when($categoryId, function ($query) use ($categoryId) {
            $query->where('category_id', $categoryId);
        })
            ->whereHas('hearts', function ($query) use ($regionId) {
                $query->when($regionId, function ($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                });
            })
            ->withCount(['hearts' => function ($query) use ($regionId) {
                $query->when($regionId, function ($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                });
            }])
            ->orderByDesc('hearts_count')
            ->take(50)
            ->get();
            // $products->makeHidden(['total_heart']);
        if ($products->isEmpty()) {
            return response()->error(
                'No hearted products found.',
                404
            );
        }

        return response()->success(
            $products,
            'Most hearted products retrieved successfully.'
        );
    }

    //ad requests products
    public function adRequestsProducts(Request $request)
    {
        $adRequestsProducts = ModelsTrendingProducts::with(['product:id,product_name,product_image,user_id,product_price'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->take(8)
            ->get();

        if ($adRequestsProducts->isEmpty()) {
            return response()->error(
                'No ad requests products found.',
                404
            );
        }
        $adRequestsProducts->transform(function ($adRequest) {
            return [
                'id' => $adRequest->id,
                'product_id' => $adRequest->product_id,
                'product_name' => $adRequest->Product->product_name ?? null,
                'product_image' => $adRequest->Product->product_image ?? null,
                'product_price' => $adRequest->Product->product_price ?? null,
                'total_heart' => $adRequest->Product->total_heart ?? 0,
                'is_hearted' => $adRequest->Product->is_hearted ?? false,
                'user_id' => $adRequest->user_id ?? null,
                'status' => $adRequest->status,
                'is_active' => $adRequest->is_active,
                'average_rating' => $adRequest->Product->average_rating ?? 0,
                'display_order' => $adRequest->display_order,
                'created_at' => $adRequest->created_at,
            ];
        });
        return response()->success(
            $adRequestsProducts,
            'Ad requests products retrieved successfully.'
        );
    }

    public function mostFollowersBrand(Request $request)
    {

        $brands = User::where('role', Role::BRAND->value)
            ->whereHas('followers')
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->take(50)
            ->get();

        if ($brands->isEmpty()) {
            return response()->error(
                'No brands found.',
                404
            );
        }

        return response()->success(
            $brands,
            'Most followed brands retrieved successfully.'
        );
    }
}
