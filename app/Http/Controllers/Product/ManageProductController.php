<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ManageProductRequest;
use App\Models\ManageProduct;
use App\Services\Products\ManageProductsService;
use Illuminate\Http\Request;

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
        $products = $this->manageProduct->getAllProducts();
        if (empty($products)) {
            return response()->errorResponse('No products found.', 404);
        }
        return response()->successResponse($products, 'Products retrieved successfully.');
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

            if ($product) {
                return response()->successResponse(
                    $product,
                    'Product created successfully.'
                );
            }
        } catch (\Exception $e) {
            return response()->errorResponse(
                'Error storing product',
                500,
                env('APP_DEBUG') === 'true' ? $e->getMessage() : null);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = $this->manageProduct->getProductById((int)$id);
            if (empty($product)) {
                return response()->errorResponse('Product not found.', 404);
            }
            return response()->successResponse($product, 'Product retrieved successfully.');
        } catch (\Exception $e) {
            return response()->errorResponse(
                'Error retrieving product',
                500,
                env('APP_DEBUG') === 'true' ? $e->getMessage() : null
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

            return response()->successResponse(
                $updatedProduct,
                'Product updated successfully.'
            );
        } catch (\Exception $e) {
            return response()->errorResponse(
                'Error updating product',
                500,
                env('APP_DEBUG') === 'true' ? $e->getMessage() : null
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
                return response()->successResponse(
                    null,
                    'Product deleted successfully.'
                );
            }
            return response()->errorResponse(
                'Product not found',
                404
            );
        } catch (\Exception $e) {
            return response()->errorResponse(
                'Error deleting product',
                500,
                env('APP_DEBUG') === 'true' ? $e->getMessage() : null
            );
        }
    }
}
