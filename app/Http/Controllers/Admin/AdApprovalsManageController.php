<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdRequestResource;
use App\Models\FeaturedAd;
use App\Models\MostFollowerAd;
use App\Models\TrendingProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdApprovalsManageController extends Controller
{
    // get all ad requests
    public function getAllAdRequests(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $type = $request->input('type', 'products'); // or followers
        $category_id = $request->input('category_id', null);
        $region_id = $request->input('region_id', null);

        //query based on type
        $adRequests = match ($type) {
            'products' => TrendingProducts::with(['product:id,product_name,product_image,user_id', 'user:id,first_name,last_name,avatar,role', 'category', 'region.country'])
                ->when($search, function ($query, $search) {
                    return $query->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', 'like', '%' . $search . '%');
                    });
                }),
            'followers' => MostFollowerAd::with(['user:id,first_name,last_name', 'region.country'])
                ->when($search, function ($query, $search) {
                    return $query->whereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
                }),
            'featured' => FeaturedAd::with(['user:id,first_name,last_name,avatar', 'region.country', 'FeaturedArticle:id,title,article_image,content'])
                ->when($search, function ($query, $search) {
                    return $query->whereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%');
                    });
                }),
            // Add other types if needed
            default => TrendingProducts::with(['product:id,product_name,product_image,user_id', 'payments'])
                ->when($search, function ($query, $search) {
                    return $query->whereHas('product', function ($q) use ($search) {
                        $q->where('product_name', 'like', '%' . $search . '%');
                    });
                }),
        };

        $adRequests = match ($filter) {
            'pending' => $adRequests->where('status', 'pending'),
            'approved' => $adRequests->where('status', 'approved'),
            'rejected' => $adRequests->where('status', 'rejected'),
            'active' => $adRequests->where('is_active', true),
            'inactive' => $adRequests->where('is_active', false),
            'expired' => $adRequests->where('status', 'expired'),
            default => $adRequests,
        };

        //filter by category and region if provided
        if ($category_id && $type === 'products') {
            $adRequests = $adRequests->where('category_id', $category_id);
        }
        if ($region_id) {
            $adRequests = $adRequests->where('region_id', $region_id);
        }

        $adRequests = $adRequests->orderBy('created_at', 'desc')->paginate($perPage);


        if ($adRequests->isEmpty()) {
            return response()->error('No ad requests found.', 404);
        }
        return AdRequestResource::collection($adRequests)->additional([
            'ok' => true,
            $type === 'products' ?: 'message' => $type === 'products' ? 'Trending ad products retrieved successfully.' : 'Most followers ads retrieved successfully.'
        ]);
    }

    //get ad request by id
    public function getAdRequestById($id)
    {
        $type = request()->input('type', 'products'); // or followers
        $adRequest = match ($type) {
            'products' => TrendingProducts::with(['product:id,product_name,product_image,user_id', 'user', 'category', 'region'])->find($id),
            'followers' => MostFollowerAd::with(['user:id,first_name,last_name', 'payments'])->find($id),
            'featured' => FeaturedAd::with(['user:id,first_name,last_name,avatar', 'region.country', 'FeaturedArticle'])->find($id),
            default => TrendingProducts::with(['product:id,product_name,product_image,user_id', 'payments'])->find($id),
        };

        if (!$adRequest) {
            return response()->error('Ad request not found.', 404);
        }

        return response()->success(new AdRequestResource($adRequest), 'Ad request retrieved successfully.');
    }


    //update ad request status
    public function updateAdRequestStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,approved,rejected,expired',
                'is_active' => 'required|boolean',
                'display_order' => 'nullable|integer|min:1|max:8',
                'rejection_reason' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->error($validator->errors()->first(), 422, $validator->errors());
            }
            $type = $request->input('type', 'products'); // or followers

            $adRequest = match ($type) {
                'products' => TrendingProducts::find($id),
                'followers' => MostFollowerAd::find($id),
                'featured' => FeaturedAd::find($id),
                default => TrendingProducts::find($id),
            };
            if (!$adRequest) {
                return response()->error('Ad request not found.', 404);
            }

            // dd($adRequest);

            $status = $request->input('status');
            if ($status === 'approved') {
                $adRequest->approved_by = auth()->user()->id;
                $adRequest->approved_at = now();
            } else if ($status === 'rejected') {
                $adRequest->rejected_by = auth()->user()->id;
                $adRequest->rejected_at = now();
                $adRequest->rejection_reason = $request->input('rejection_reason', null);
            }
            $is_active = $request->input('is_active', false);
            if ($is_active && $status !== 'approved') {
                return response()->error('Ad request must be approved to be active.', 422);
            }
            if ($is_active) {
                $adRequest->start_date = now();
                [$start, $end] = match ($adRequest->preferred_duration) {
                    '1_week' => [now(), now()->addWeek()],
                    '2_weeks' => [now(), now()->addWeeks(2)],
                    '1_month' => [now(), now()->addMonth()],
                    '3_months' => [now(), now()->addMonths(3)],
                    '6_months' => [now(), now()->addMonths(6)],
                    default => [null, null],
                };
                $adRequest->start_date = $start;
                $adRequest->end_date = $end;
            }

            $adRequest->status = $request->input('status');
            $adRequest->is_active = $is_active;

            $adRequest->display_order = $request->input('display_order', null);
            $adRequest->save();

            return response()->success($adRequest, 'Ad request status updated successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to update ad request status.', 500, $e->getMessage());
        }
    }
}
