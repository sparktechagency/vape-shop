<?php

namespace App\Http\Controllers\Product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\StoreProduct;
use App\Models\User;
use App\Repositories\Products\HeartedProductsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $role = (int) $request->input('role');
        // Build validation rules based on role
        $productRule = $this->ProductRule($role);
        // Validate the request
        $validator = Validator::make($request->all(), [
            'role' => 'required|integer|in:' . Role::BRAND->value . ',' . Role::STORE->value . ',' . Role::WHOLESALER->value,
            'product_id' => $productRule,
        ]);
        //validate errors
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        switch ($role) {
            case Role::BRAND->value:
                $reviews = Review::where('manage_product_id', $request->input('product_id'))
                    ->with(['manageProducts:id,user_id,product_name,product_image', 'user:id,first_name,last_name,email,role'])
                    ->paginate(10);
                break;
            case Role::STORE->value:
                $reviews = Review::where('store_product_id', $request->input('product_id'))
                    ->with(['storeProducts:id,product_name,product_image','user:id,first_name,last_name,email,role'])
                    ->paginate(10);
                break;
            case Role::WHOLESALER->value:
                // Handle wholesaler reviews logic here
                break;
            default:
                return response()->error('Invalid role provided.', 400);
        }

        // Return the reviews
        return response()->success($reviews, 'Reviews retrieved successfully.', 200);


    }

    //product rule based on role
    private function ProductRule($role)
    {
        switch ($role) {
            case Role::BRAND->value:
                return 'required|exists:manage_products,id';
            case Role::STORE->value:
                return 'required|exists:store_products,id';
            case Role::WHOLESALER->value:
                return 'required|exists:wholesaler_products,id';
            default:
                return 'required|exists:manage_products,id';
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
    public function store(Request $request)
    {

        $role = (int)$request->input('role');
        // Build validation rules based on role
        $productRule = $this->ProductRule($role);
        // Validate the request
        $validator = Validator::make($request->all(), [
            'product_id' => $productRule,
            'role' => 'required|integer|in:' . Role::BRAND->value . ',' . Role::STORE->value . ',' . Role::WHOLESALER->value,
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        //validate errors
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        // Create the review
        switch ($role) {
            case Role::BRAND->value:
                $review = Review::create([
                    'user_id' => auth()->id(),
                    'manage_product_id' => $request->input('product_id'),
                    'rating' => $request->input('rating'),
                    'comment' => $request->input('comment'),
                ]);
                break;
            case Role::STORE->value:
                $data = $this->getProductAndRegionId($request->input('product_id'), Role::STORE->value);
                $review = Review::create([
                    'user_id' => auth()->id(),
                    'store_product_id' => $request->input('product_id'),
                    'region_id' => $data['region_id'] ?? null,
                    'manage_product_id' => $data['manage_product_id'] ?? null,
                    'rating' => $request->input('rating'),
                    'comment' => $request->input('comment'),
                ]);
                break;
            case Role::WHOLESALER->value:
                // Handle wholesaler review creation logic here
                break;
            default:
                return response()->error('Invalid role provided.', 400);
        }

        // Return the created review
        return response()->success($review, 'Review created successfully.', 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->error('Review not found.', 404);
        }

        // Check if the authenticated user is the owner of the review
        if ($review->user_id !== auth()->id()) {
            return response()->error('You are not authorized to delete this review.', 403);
        }

        // Delete the review
        $review->delete();

        return response()->success(null, 'Review deleted successfully.', 200);
    }



    private function getProductAndRegionId($productId, $role)
    {
        if ($role === Role::STORE->value) {
            $data = [];
            $product = StoreProduct::find($productId);
            if ($product) {
                if ($product->user_id && User::find($product->user_id)->address) {
                    $data['region_id'] = User::find($product->user_id)->address->region_id;
                }
                // dd($product->manageProducts);
                if ($product->product_id && $product->manageProducts) {
                    $data['manage_product_id'] = $product->product_id;
                } else {
                    $data['manage_product_id'] = null;
                }
            }
            return $data;
        }
        return null;
    }
}
