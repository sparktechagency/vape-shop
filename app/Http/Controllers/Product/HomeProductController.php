<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Products\HomeProductService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class HomeProductController extends Controller
{
    protected $homeProductService;

    // Cache TTL
    private const CACHE_TTL = 1800; // 30 minutes

    public function __construct(HomeProductService $homeProductService)
    {
        $this->homeProductService = $homeProductService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $role = request()->get('role');
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 10);

            // Generate cache key based on role and pagination
            $cacheKey = "home_products_role_{$role}_page_{$page}_per_page_{$perPage}";

            // Use cache with tags
            $products = Cache::tags(['products', 'home'])->remember($cacheKey, self::CACHE_TTL, function () use ($role) {
                return $this->homeProductService->getAllProducts((int)$role);
            });

            if (!empty($products) && isset($products['data']) && !empty($products['data'])) {
                return response()->success($products, 'Products retrieved successfully.');
            }
            return response()->error('No products found.', 404);
        } catch (\Exception $e) {
            return response()->error(
                'Error retrieving products',
                500,
                $e->getMessage()
            );
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $role = request()->get('role');

            // Generate cache key based on product ID and role
            $cacheKey = "home_product_{$id}_role_{$role}";

            // Use cache with tags
            $product = Cache::tags(['products', 'home'])->remember($cacheKey, self::CACHE_TTL, function () use ($id, $role) {
                return $this->homeProductService->getProductById((int)$id, (int)$role);
            });

            if (!empty($product)) {
                return response()->success($product, 'Product retrieved successfully.');
            }
            return response()->error('Product not found.', 404);
        } catch (\Exception $e) {
            return response()->error(
                'Error retrieving product',
                500,
                $e->getMessage()
            );
        }
    }

    //get all categories
    public function getAllCategories()
    {
        try {
            // Use cache with tags for categories
            $categories = Cache::tags(['categories', 'home'])->remember('all_categories', self::CACHE_TTL, function () {
                return Category::all();
            });

            if ($categories->isEmpty()) {
                return response()->error('No categories found.', 404);
            }
            return response()->success($categories, 'Categories retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error(
                'Error retrieving categories',
                500,
                $e->getMessage()
            );
        }

    }
}
