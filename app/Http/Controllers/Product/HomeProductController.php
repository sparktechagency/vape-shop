<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Products\HomeProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeProductController extends Controller
{
    protected $homeProductService;
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
            $products = $this->homeProductService->getAllProducts((int)$role);
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
            $product = $this->homeProductService->getProductById((int)$id, (int)$role);
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
            $categories = Category::all();
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
