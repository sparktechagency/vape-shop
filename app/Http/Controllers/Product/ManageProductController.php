<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ManageProductRequest;
use App\Models\ManageProduct;
use App\Services\Products\ManageProductsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageProductController extends Controller
{
    protected $manageProduct;

    public function __construct(ManageProductsService $manageProductsService)
    {
        $this->manageProduct = $manageProductsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = $this->manageProduct->getAllProducts();
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ManageProductRequest $request)
    {
        try {
            $data = $request->validated();
            $productImage = $request->file('product_image') ?? null;
            $product = $this->manageProduct->storeProduct($data, $productImage);

            return response()->success(
                $product,
                'Product created successfully.'
            );
        } catch (\Exception $e) {
            return response()->error(
                'Error creating product',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // dd(Auth::user()->role === Role::STORE->value);
            $product = $this->manageProduct->getProductById((int)$id);
            if (empty($product)) {
                return response()->error('Product not found.', 404);
            }
            return response()->success($product, 'Product retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error(
                $e->getMessage(),
                500,
                'Error retrieving product',
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ManageProductRequest $request, string $id)
    {
        try {
            $data = $request->validated();
            $productImage = $request->file('product_image') ?? null;
            $updatedProduct = $this->manageProduct->updateProduct((int)$id, $data, $productImage);

            return response()->success(
                $updatedProduct,
                'Product updated successfully.'
            );
        } catch (\Exception $e) {
            return response()->error(
                'Error updating product',
                500,
                $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deleted = $this->manageProduct->deleteProduct((int)$id);
            if ($deleted) {
                return response()->success(
                    null,
                    'Product deleted successfully.'
                );
            }
            return response()->error(
                'Product not found',
                404
            );
        } catch (\Exception $e) {
            return response()->error(
                'Error deleting product',
                500,
                $e->getMessage()
            );
        }
    }
}
