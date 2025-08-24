<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\ManageProduct;
use App\Models\TrendingProducts as ModelsTrendingProducts;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TrendingProducts extends Controller
{
        // Cache configuration
    private const CACHE_TTL = 3600; // 1 hour
    private const HEARTS_CACHE_PREFIX = 'trending_most_hearted';
    private const AD_REQUESTS_CACHE_PREFIX = 'trending_ad_requests';
    private const FOLLOWERS_CACHE_PREFIX = 'trending_most_followers';
    private const MOST_FOLLOWERS_CACHE_PREFIX = 'trending:most_followers';

    /**
     * Generate cache key based on parameters
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $paramString = http_build_query($params);
        return $prefix . '_' . md5($paramString);
    }

    //most hearted products
    public function mostHeartedProducts(Request $request)
    {
        $regionId = request('region_id');
        $categoryId = request('category_id');

        // Generate cache key based on parameters
        $cacheKey = $this->generateCacheKey(self::HEARTS_CACHE_PREFIX, [
            'region_id' => $regionId,
            'category_id' => $categoryId
        ]);

        // Use cache for most hearted products with tags
        $products = Cache::tags(['products', 'trending', 'hearts'])->remember($cacheKey, self::CACHE_TTL, function () use ($regionId, $categoryId) {
            return ManageProduct::when($categoryId, function ($query) use ($categoryId) {
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
        });

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
        $regionId = $request->input('region_id');
        $categoryId = $request->input('category_id');

        // Generate cache key based on parameters
        $cacheKey = $this->generateCacheKey(self::AD_REQUESTS_CACHE_PREFIX, [
            'region_id' => $regionId,
            'category_id' => $categoryId
        ]);

        // Use cache for ad requests products with tags
        $adRequestsProducts = Cache::tags(['products', 'trending', 'ads'])->remember($cacheKey, self::CACHE_TTL, function () use ($regionId, $categoryId) {
            $query = ModelsTrendingProducts::with(['product:id,product_name,product_image,user_id,product_price'])
                ->where('status', 'approved')
                ->where('is_active', true);

            if ($regionId) {
                $query->where('region_id', $regionId);
            }
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            return $query->orderBy('display_order')
                ->take(8)
                ->get();
        });

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
                'category_id' => $adRequest->category_id,
                'region_id' => $adRequest->region_id,
                'preferred_duration' => $adRequest->preferred_duration,
                'amount' => $adRequest->amount,
                'slot' => $adRequest->slot,
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
        $regionId = $request->input('region_id');

        // Generate cache key based on parameters
        $cacheKey = $this->generateCacheKey(self::FOLLOWERS_CACHE_PREFIX, [
            'region_id' => $regionId
        ]);

        // Use cache for most followers brand with tags
        $brands = Cache::tags(['users', 'trending', 'followers'])->remember($cacheKey, self::CACHE_TTL, function () use ($regionId) {
            return User::with('address')->where('role', Role::BRAND->value)
                ->whereHas('address', function ($query) use ($regionId) {
                    $query->when($regionId, function ($q) use ($regionId) {
                        $q->where('region_id', $regionId);
                    });
                })
                ->whereHas('followers')
                ->withCount('followers')
                ->orderByDesc('followers_count')
                ->take(50)
                ->get();
        });

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
