<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdSlot;
use App\Models\Category;
use App\Models\Region;
use App\Models\AdPricing;
use Illuminate\Support\Facades\Validator;

class AdPricingController extends Controller
{
    /**
     * Fetch all necessary data for the ad management interface.
     * Optionally filters pricings by category and region.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'region_id' => 'sometimes|exists:regions,id',
            'ad_slot_id' => 'required|exists:ad_slots,id',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }
        $categoryId = $request->query('category_id');
        $regionId = $request->query('region_id');
        $adSlotsId = $request->query('ad_slot_id');
        $adPricing = AdPricing::with(['adSlot'])
            ->where('ad_slot_id', $adSlotsId)
            ->when($categoryId, function ($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->when($regionId, function ($query) use ($regionId) {
                return $query->where('region_id', $regionId);
            })
            ->get();

         if ($adPricing->isEmpty()) {
            return response()->error('No ad pricing found for the given criteria.', 404);
        }


        return response()->success( $adPricing, 'Ad pricings fetched successfully.');
    }


    /**
     * Store or update the pricing information for a specific ad slot via API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveOrUpdate(Request $request)
    {
        $validatedData = $request->validate([
             'ad_slot_id' => 'required|exists:ad_slots,id',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'required|exists:regions,id',
            'description' => 'nullable|string|max:1000',
            'weekly_prices' => 'required|array|size:4',
            'weekly_prices.week_1' => 'required|numeric|min:0',
            'weekly_prices.week_2' => 'required|numeric|min:0',
            'weekly_prices.week_3' => 'required|numeric|min:0',
            'weekly_prices.week_4' => 'required|numeric|min:0',
        ]);


        $pricing = AdPricing::updateOrCreate(
            [
                // Keys to find the existing record
                'ad_slot_id' => $validatedData['ad_slot_id'],
                'category_id' => $validatedData['category_id'],
                'region_id' => $validatedData['region_id'],
            ],
            [
                // Values to update or create with
                'details' => $validatedData['weekly_prices'],
                'description' => $validatedData['description'],
            ]
        );

        return response()->json([
            'message' => 'Pricing for the slot has been saved successfully!',
            'data' => $pricing->fresh()
        ], 200);
    }

    //delete pricing
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

