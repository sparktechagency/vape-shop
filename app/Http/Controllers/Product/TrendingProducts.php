<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\ManageProduct;
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
