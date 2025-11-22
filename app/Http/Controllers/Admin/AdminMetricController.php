<?php

namespace App\Http\Controllers\Admin;

use App\Models\MetricAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

// Import Models
use App\Models\StoreProduct;
use App\Models\ManageProduct; // Used for Brands
use App\Models\WholesalerProduct;
use App\Models\User;

class AdminMetricController extends Controller
{
    /**
     * Update metric counts based on ID and Entity Type.
     */
    public function storeOrUpdate(Request $request)
    {
        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'target_id'   => 'required|integer',
            'target_type' => 'required|string|in:shop,brand,wholesaler,user',
            'metric_type' => 'required|in:follower,heart,upvote',
            'count'       => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            // 2. Map target_type string to actual Model Class
            $modelClass = match ($request->target_type) {
                'shop'       => StoreProduct::class,
                'brand'      => ManageProduct::class,      // As per your structure, Brand uses ManageProduct
                'wholesaler' => WholesalerProduct::class,
                'user'       => User::class,
                default      => null,
            };

            if (!$modelClass) {
                return response()->json(['ok' => false, 'message' => 'Invalid target type.'], 400);
            }

            // 3. Find the specific record by ID
            $targetModel = $modelClass::find($request->target_id);

            if (!$targetModel) {
                return response()->json(['ok' => false, 'message' => 'Target entity not found with the provided ID.'], 404);
            }

            // 4. Save or Update the adjustment in metric_adjustments table
            MetricAdjustment::updateOrCreate(
                [
                    'adjustable_id'   => $targetModel->id,
                    'adjustable_type' => get_class($targetModel), // Stores App\Models\StoreProduct etc.
                    'metric_type'     => $request->metric_type,
                ],
                [
                    'adjustment_count' => $request->count
                ]
            );

            return response()->json(['ok' => true, 'message' => 'Interaction count updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Get the current fake count for a specific entity.
     */
    public function getMetric(Request $request)
    {
        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'target_id'   => 'required|integer',
            'target_type' => 'required|string|in:shop,brand,wholesaler,user',
            'metric_type' => 'required|in:follower,heart,upvote',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $modelClass = match ($request->target_type) {
                'shop'       => \App\Models\StoreProduct::class,
                'brand'      => \App\Models\ManageProduct::class,
                'wholesaler' => \App\Models\WholesalerProduct::class,
                'user'       => \App\Models\User::class,
                default      => null,
            };

            if (!$modelClass) {
                return response()->json(['ok' => false, 'message' => 'Invalid target type.'], 400);
            }

            $adjustment = MetricAdjustment::where('adjustable_id', $request->target_id)
                ->where('adjustable_type', $modelClass)
                ->where('metric_type', $request->metric_type)
                ->first();

            return response()->json([
                'ok' => true,
                'data' => [
                    'count' => $adjustment ? $adjustment->adjustment_count : 0,
                    'target_id' => (int)$request->target_id,
                    'target_type' => $request->target_type,
                    'metric_type' => $request->metric_type
                ],
                'message' => 'Interaction retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Get list of all entities with fake counts.
     */
    public function getAllAdjustments(Request $request)
    {
        // Eager load the 'adjustable' model (Shop, User, Brand, etc.)
        $adjustments = MetricAdjustment::with('adjustable')
            ->latest()
            ->paginate(20);

        $formattedData = $adjustments->getCollection()->map(function ($item) {
            $entity = $item->adjustable;
            $name = 'Unknown/Deleted';


            if ($entity) {
                if ($item->adjustable_type === \App\Models\User::class) {
                    $name = $entity->full_name ?? $entity->first_name ?? 'User';
                } elseif ($item->adjustable_type === \App\Models\StoreProduct::class) {
                    $name = $entity->product_name ?? $entity->name ?? 'Shop Item';
                } elseif ($item->adjustable_type === \App\Models\ManageProduct::class) {
                    $name = $entity->product_name ?? 'Brand Item';
                } elseif ($item->adjustable_type === \App\Models\WholesalerProduct::class) {
                    $name = $entity->product_name ?? 'Wholesaler Item';
                }
            }

            return [
                'id'               => $item->id,
                'target_id'        => $item->adjustable_id,
                'target_type'      => class_basename($item->adjustable_type),
                'target_name'      => $name,
                'metric_type'      => $item->metric_type,
                'fake_count'       => $item->adjustment_count,
                'last_updated'     => $item->updated_at->format('Y-m-d H:i A'),
            ];
        });

        $adjustments->setCollection($formattedData);

        return response()->json([
            'ok' => true,
            'data' => $adjustments,
            'message' => 'Adjusted Interactions list retrieved successfully.'
        ]);
    }
}
