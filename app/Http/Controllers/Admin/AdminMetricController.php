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

        $validator = Validator::make($request->all(), [
            'target_id'   => 'required|integer',
            'target_type' => 'required|string|in:user,shop,brand,wholesaler,post',
            'metric_type' => 'required|string|in:follower,heart,upvote',
            'count'       => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {


            $metric = $request->metric_type;
            $targetType = $request->target_type;

            if ($metric === 'follower' && $targetType !== 'user') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid Request: Followers can only be increased for Users.'
                ], 400);
            }


            if ($metric === 'upvote' && $targetType !== 'post') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid Request: Upvotes can only be increased for Posts.'
                ], 400);
            }

            if ($metric === 'heart' && $targetType === 'user') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Invalid Request: Hearts cannot be assigned to a User directly.'
                ], 400);
            }

            $modelClass = match ($targetType) {
                'user'       => User::class,
                'shop'       => StoreProduct::class,
                'brand'      => ManageProduct::class,
                'wholesaler' => WholesalerProduct::class,
                'post'       => Post::class,
                default      => null,
            };

            if (!$modelClass) {
                return response()->json(['ok' => false, 'message' => 'Invalid target type specified.'], 400);
            }


            $targetModel = $modelClass::find($request->target_id);

            if (!$targetModel) {

                return response()->json([
                    'ok' => false,
                    'message' => ucfirst($targetType) . ' not found with the provided ID: ' . $request->target_id
                ], 404);
            }

            MetricAdjustment::updateOrCreate(
                [
                    'adjustable_id'   => $targetModel->id,
                    'adjustable_type' => get_class($targetModel),
                    'metric_type'     => $metric,
                ],
                [
                    'adjustment_count' => $request->count
                ]
            );

            return response()->json([
                'ok' => true,
                'message' => ucfirst($metric) . ' count updated successfully for ' . ucfirst($targetType) . '.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get the current fake count for a specific entity.
     */
    public function getMetric(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_id'   => 'required|integer',
            'target_type' => 'required|string|in:shop,brand,wholesaler,user,post',
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
                'post'       => \App\Models\Post::class,
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
        $perPage = $request->input('per_page', 20);
        $metricType = $request->input('metric_type');

        $query = MetricAdjustment::with('adjustable');

        if ($metricType) {
            $query->where('metric_type', $metricType);
        }

        $adjustments = $query->latest()->paginate($perPage);

        $formattedData = $adjustments->getCollection()->map(function ($item) {
            $entity = $item->adjustable;

            $name = 'Unknown/Deleted';
            $avatar = asset('images/default-avatar.png'); // ডিফল্ট ইমেজ
            $realCount = 0;

            if ($entity) {
                // ==========================================
                // ১. নাম এবং অবতার সেটআপ
                // ==========================================
                if ($item->adjustable_type === \App\Models\User::class) {
                    $name = $entity->full_name ?? $entity->first_name ?? 'User';
                    $avatar = $entity->avatar;
                } elseif ($item->adjustable_type === \App\Models\StoreProduct::class) {
                    $name = $entity->product_name ?? $entity->name ?? 'Shop Item';
                    $avatar = $entity->image ? asset('storage/' . $entity->image) : asset('images/default-product.png');
                } elseif ($item->adjustable_type === \App\Models\ManageProduct::class) {
                    $name = $entity->product_name ?? 'Brand Item';
                    $avatar = $entity->image ? asset('storage/' . $entity->image) : asset('images/default-product.png');
                } elseif ($item->adjustable_type === \App\Models\WholesalerProduct::class) {
                    $name = $entity->product_name ?? 'Wholesaler Item';
                    $avatar = $entity->image ? asset('storage/' . $entity->image) : asset('images/default-product.png');
                } elseif ($item->adjustable_type === \App\Models\Post::class) {
                    // <--- নতুন POST লজিক
                    $name = $entity->title ?? Str::limit($entity->content, 30) ?? 'Untitled Post';

                    // পোস্টের ছবি: যদি আর্টিকেল হয় তবে article_image, আর গ্যালারি হলে ১ম ছবি
                    if ($entity->content_type === 'article' && $entity->article_image) {
                        $avatar = asset('storage/' . $entity->article_image);
                    } elseif ($entity->images && $entity->images->first()) {
                        $avatar = asset('storage/' . $entity->images->first()->image_path);
                    } else {
                        $avatar = asset('images/default-post.png');
                    }
                }


                // ==========================================
                // ২. রিয়েল কাউন্ট লজিক
                // ==========================================
                switch ($item->metric_type) {
                    case 'follower':
                        if (method_exists($entity, 'followers')) {
                            $realCount = $entity->followers()->count();
                        }
                        break;

                    case 'upvote': // <--- নতুন: পোস্টের জন্য
                        // ফিড লাইক (যাকে আমরা upvote বলছি)
                        if ($item->adjustable_type === \App\Models\Post::class && method_exists($entity, 'likes')) {
                            $realCount = $entity->likes()->count();
                        }
                        break;

                    case 'heart':
                        // যদি পোস্ট হয় (গ্যালারি হার্ট)
                        if ($item->adjustable_type === \App\Models\Post::class) {
                            if (method_exists($entity, 'hearts')) {
                                $realCount = $entity->hearts()->count();
                            }
                        }
                        // যদি প্রোডাক্ট বা ইউজার হয়
                        elseif (method_exists($entity, 'favouritesBy')) {
                            $realCount = $entity->favouritesBy()->count();
                        } elseif (method_exists($entity, 'favourites')) {
                            $realCount = $entity->favourites()->count();
                        }
                        break;

                    case 'review':
                    case 'rating':
                        if (method_exists($entity, 'reviews')) {
                            $realCount = $entity->reviews()->count();
                        }
                        break;

                    default:
                        $realCount = 0;
                }
            }

            return [
                'id'             => $item->id,
                'target_id'      => $item->adjustable_id,
                'target_type'    => class_basename($item->adjustable_type),
                'target_name'    => $name,
                'target_avatar'  => $avatar,
                'metric_type'    => $item->metric_type,
                'fake_count'     => $item->adjustment_count,
                'real_count'     => $realCount,
                'total_display'  => $realCount + $item->adjustment_count,
                'last_updated'   => $item->updated_at->format('Y-m-d H:i A'),
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
