<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\B2bProductResource;
use Illuminate\Http\Request;
use App\Models\B2bPricing;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class B2bPricingController extends Controller
{
    /**
     * Store or update B2B pricing for a specific product.
     */
    public function storeOrUpdate(Request $request)
    {
        // Validation Logic
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

        // Match user role to model class
        $productableType = match ($seller->role) {
            Role::STORE->value => \App\Models\StoreProduct::class,
            Role::WHOLESALER->value => \App\Models\WholesalerProduct::class,
            Role::BRAND->value => \App\Models\ManageProduct::class,
            default => null,
        };

        if (!$productableType) {
            return response()->error('Invalid user role to set B2B price.', 403);
        }


        $exists = $productableType::where('id', $validated['productable_id'])
            ->where('user_id', $seller->id)
            ->exists();

        if (!$exists) {
            return response()->error('Product not found or you do not own this product.', 404);
        }

        $b2bPricing = B2bPricing::updateOrCreate(
            [
                'productable_id' => $validated['productable_id'],
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

    /**
     * Remove B2B pricing (Stop selling as B2B).
     */
    public function destroy($productable_id)
    {
        $seller = Auth::user();

        // Find the pricing entry that belongs to this seller
        $pricing = B2bPricing::where('productable_id', $productable_id)
        ->where('seller_id', $seller->id)
        ->first();

        if (!$pricing) {
            return response()->error('B2B pricing not found or access denied.', 404);
        }

        $pricing->delete();

        return response()->success(null, 'B2B pricing removed successfully.');
    }

    /**
     * Get auth user's B2B products (Optimized).
     */
    public function getB2bProducts(Request $request)
    {
        try {
            $seller = Auth::user();
            $perPage = $request->input('per_page', 15);

            $b2bPricings = B2bPricing::with('productable') // Eager load the product
                ->where('seller_id', $seller->id)
                ->paginate($perPage);

            if ($b2bPricings->isEmpty()) {
                return response()->error('No B2B products found for this user.', 404);
            }


            $b2bPricings->getCollection()->transform(function ($pricing) {
                $product = $pricing->productable;
                if ($product) {

                    $product->setRelation('b2bPricing', $pricing);
                }
                return $product;
            });


            $filteredCollection = $b2bPricings->getCollection()->filter();
            $b2bPricings->setCollection($filteredCollection);

            return B2bProductResource::collection($b2bPricings)->additional([
                'ok' => true,
                'message' => 'B2B products retrieved successfully.',
                'status' => 200,
            ]);

        } catch (\Exception $e) {
            return response()->error('Failed to retrieve B2B products', 500, $e->getMessage());
        }
    }

    /**
     * List products of a specific seller for a buyer.
     */
    public function listProductsOfSeller(Request $request, User $seller)
    {
        try {
            $buyer = Auth::user();

            // Check approval
            $isApproved = $buyer->b2bProviders()
                ->where('provider_id', $seller->id)
                ->where('status', 'approved')
                ->exists();

            if (!$isApproved) {
                return response()->error('You do not have an approved B2B connection.', 403);
            }

            $perPage = $request->input('per_page', 15);


            $b2bPricings = B2bPricing::with('productable')
                ->where('seller_id', $seller->id)
                ->paginate($perPage);


            $b2bPricings->getCollection()->transform(function ($pricing) {
                $product = $pricing->productable;
                if ($product) {
                    $product->setRelation('b2bPricing', $pricing);
                }
                return $product;
            });

            // Filter nulls
            $b2bPricings->setCollection($b2bPricings->getCollection()->filter());

            return B2bProductResource::collection($b2bPricings)->additional([
                'ok' => true,
                'message' => 'Products retrieved successfully.',
                'status' => 200,
            ]);

        } catch (\Exception $e) {
            return response()->error('Failed to retrieve products', 500, $e->getMessage());
        }
    }
}
