<?php

namespace App\Http\Controllers\product;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Services\Products\HeartedProductsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HeartedProductController extends Controller
{
    protected $heartProduct;
    public function __construct(HeartedProductsService $heartProduct)
    {
        $this->heartProduct = $heartProduct;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $userId = request()->query('user_id') ?? Auth::id();
            $result = $this->heartProduct->getHeartedProductsByUserId( (int) $userId);
            return $result;
            if (!empty($result) && isset($result['data']) && !empty($result['data'])) {
                return response()->success($result, 'Hearted products retrieved successfully', 200);
            }else {
                return response()->error('No hearted products found for this user', 404);
            }
        }catch (\Exception $e){
            return response()->error('Error occurred while retrieving hearted products', 500, $e->getMessage());
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
        try{
            $role = (int)$request->input('role');
            // Build validation rules based on role
            $productRule = match ($role) {
                Role::BRAND => 'required|exists:manage_products,id',
                Role::STORE => 'required|exists:store_products,id',
                Role::WHOLESALER => 'required|exists:wholesaler_products,id',
                default => 'required|exists:manage_products,id',
            };

            $validator = Validator::make($request->all(), [
                'product_id' => $productRule,
                'role' => 'required|in:' . Role::BRAND . ',' . Role::STORE . ',' . Role::WHOLESALER,
            ]);
            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }
            $userId = Auth::id();
            $productId = $request->input('product_id');

            // return $productId;
            $result = $this->heartProduct->toggleHeartedProduct($productId, $userId, $role);

            // dd($result);
            if ($result === true) {
                return response()->success($result, 'Product hearted successfully', 200);
            }else {
                return response()->success($result, 'Product unhearted successfully', 200);
            }


        }catch (\Exception $e){
            return response()->error('Error occurred while retrieving comments', 500, $e->getMessage());
        }
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
        //
    }
}
