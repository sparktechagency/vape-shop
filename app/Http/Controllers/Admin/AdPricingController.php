<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdSlot;
use App\Models\Category;
use App\Models\Region;
use App\Models\AdPricing;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdPricingController extends Controller
{
    /**
     * Fetch all necessary data for the ad management interface.
     * Optionally filters pricings by category, region, and type.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'region_id'   => 'sometimes|exists:regions,id',
            'ad_slot_id'  => 'required|exists:ad_slots,id',
            'type'        => ['required', Rule::in(['product', 'follower', 'featured'])],
            'category_id' => [
                Rule::requiredIf(fn() => $request->type === 'product'),
                'nullable',
                'exists:categories,id',
            ],
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $categoryId = $request->query('category_id');
        $regionId   = $request->query('region_id');
        $adSlotsId  = $request->query('ad_slot_id');
        $type       = $request->query('type');

        $adPricing = AdPricing::with(['adSlot'])
            ->where('ad_slot_id', $adSlotsId)
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId))
            ->when($regionId, fn($query) => $query->where('region_id', $regionId))
            ->when($type, fn($query) => $query->where('type', $type))
            ->get();

        if ($adPricing->isEmpty()) {
            return response()->error('No ad pricing found for the given criteria.', 404);
        }

        return response()->success($adPricing, 'Ad pricings fetched successfully.');
    }

    /**
     * Store or update the pricing information for a specific ad slot via API.
     */
    public function saveOrUpdate(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'ad_slot_id' => 'required|exists:ad_slots,id',
            'region_id'  => 'required|exists:regions,id',
            'type'       => ['required', Rule::in(['product', 'follower', 'featured'])],

            'category_id' => [
                Rule::requiredIf(fn() => $request->type === 'product'),
                'nullable',
                'exists:categories,id',
            ],

            'description' => 'nullable|string|max:1000',
            'weekly_prices' => 'required|array|size:4',
            'weekly_prices.week_1' => 'required|numeric|min:0',
            'weekly_prices.week_2' => 'required|numeric|min:0',
            'weekly_prices.week_3' => 'required|numeric|min:0',
            'weekly_prices.week_4' => 'required|numeric|min:0',
        ]);

        if ($validatedData->fails()) {
            return response()->error($validatedData->errors()->first(), 422, $validatedData->errors());
        }

        $validatedData = $validatedData->validated();

        $pricing = AdPricing::updateOrCreate(
            [
                'ad_slot_id'  => $validatedData['ad_slot_id'],
                'category_id' => $validatedData['category_id'] ?? null,
                'region_id'   => $validatedData['region_id'],
                'type'        => $validatedData['type'],
            ],
            [
                'details'     => $validatedData['weekly_prices'],
                'description' => $validatedData['description'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Pricing for the slot has been saved successfully!',
            'data'    => $pricing->fresh()
        ], 200);
    }

    /**
     * Delete pricing
     */
    public function destroy(Request $request, $id)
    {
        $pricing = AdPricing::find($id);
        if (!$pricing) {
            return response()->error('Ad pricing not found.', 404);
        }
        $pricing->delete();
        return response()->success(null, 'Ad pricing deleted successfully.');
    }
}
