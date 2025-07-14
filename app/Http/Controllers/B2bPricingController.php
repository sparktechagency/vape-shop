<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\B2bPricing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class B2bPricingController extends Controller
{
    /**
     * Store or update B2B pricing for a specific product.
     * @param Request $request
     */
    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productable_id' => 'required|integer',
            'wholesale_price' => 'required|numeric|min:0',
            'moq' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->error('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();
        $seller = Auth::user();

        $productableType = match ($seller->role) {
            Role::STORE->value => \App\Models\StoreProduct::class,
            Role::WHOLESALER->value => \App\Models\WholesalerProduct::class,
            Role::BRAND->value => \App\Models\ManageProduct::class,
            default => null,
        };

        if (!$productableType) {
            return response()->error('Invalid user role to set B2B price.', 403);
        }

        $product = $productableType::where('id', $validated['productable_id'])
            ->where('user_id', $seller->id)
            ->first();

        if (!$product) {
            return response()->error('Product not found or you do not own this product.', 404);
        }

        $b2bPricing = B2bPricing::updateOrCreate(
            [
                'productable_id' => $product->id,
                'productable_type' => $productableType,
                'seller_id' => $seller->id,
            ],
            [
                'wholesale_price' => $validated['wholesale_price'],
                'moq' => $validated['moq'],
            ]
        );

        return response()->success($b2bPricing, 'B2B pricing saved successfully.');
    }
}
