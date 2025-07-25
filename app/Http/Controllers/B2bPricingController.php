<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\B2bProductResource;
use Illuminate\Http\Request;
use App\Models\B2bPricing;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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


    //auth user b2b products
    public function getB2bProducts(Request $request)
    {
        try {
            $user = Auth::user();
            $b2bProducts = collect();
            $b2bProducts = $b2bProducts->merge($user->manageProducts()->with('b2bPricing')->get());
            $b2bProducts = $b2bProducts->merge($user->wholesalerProducts()->with('b2bPricing')->get());
            $b2bProducts = $b2bProducts->merge($user->storeProducts()->with('b2bPricing')->get());
            $b2bProducts = $b2bProducts->filter(function ($product) {
                return !is_null($product->b2bPricing);
            });

            if ($b2bProducts->isEmpty()) {
                return response()->error('No B2B products found for this user.', 404);
            }
            $perPage = $request->input('per_page', 15);
            $currentPage = Paginator::resolveCurrentPage('page');
            $paginatedProducts = new LengthAwarePaginator(
                $b2bProducts->forPage($currentPage, $perPage)->values(),
                $b2bProducts->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            return B2bProductResource::collection($paginatedProducts)->additional([
                'ok' => true,
                'message' => 'B2B products retrieved successfully.',
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve B2B products', 500, $e->getMessage());
        }
    }



    public function listProductsOfSeller(Request $request, User $seller)
    {
        try{
            $buyer = Auth::user();

        $isApproved = $buyer->b2bProviders()
            ->where('provider_id', $seller->id)
            ->where('status', 'approved')
            ->exists();

        if (!$isApproved) {
            return response()->error('You do not have an approved B2B connection to view these products.', 403);
        }

        $allProducts = collect();

        $allProducts = $allProducts->merge($seller->manageProducts()->with('b2bPricing')->get());
        $allProducts = $allProducts->merge($seller->wholesalerProducts()->with('b2bPricing')->get());
        $allProducts = $allProducts->merge($seller->storeProducts()->with('b2bPricing')->get());

        $b2bProducts = $allProducts->filter(function ($product) {
            return !is_null($product->b2bPricing);
        });


        $perPage = $request->input('per_page', 15);
        $currentPage = Paginator::resolveCurrentPage('page');

        $paginatedProducts = new LengthAwarePaginator(
            $b2bProducts->forPage($currentPage, $perPage)->values(),
            $b2bProducts->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );


        return B2bProductResource::collection($paginatedProducts)->additional([
            'ok' => true,
            'message' => 'Products retrieved successfully.',
            'status' => 200,
        ]);
        } catch (\Exception $e) {
            return response()->error('Failed to retrieve products', 500, $e->getMessage());
        }
    }
}
