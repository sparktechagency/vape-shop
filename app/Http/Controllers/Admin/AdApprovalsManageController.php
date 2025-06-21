<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $adRequests = TrendingProducts::with(['product:id,product_name,product_image,user_id'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('product', function ($q) use ($search) {
                    $q->where('product_name', 'like', '%' . $search . '%');
                });
            });

        $adRequests = match ($filter) {
            'pending' => $adRequests->where('status', 'pending'),
            'approved' => $adRequests->where('status', 'approved'),
            'rejected' => $adRequests->where('status', 'rejected'),
            'active' => $adRequests->where('is_active', true),
            'inactive' => $adRequests->where('is_active', false),
            'expired' => $adRequests->where('status', 'expired'),
            default => $adRequests,
        };
        $adRequests = $adRequests->orderBy('created_at', 'desc')->paginate($perPage);

        $adRequests->getCollection()->transform(function ($adRequest) {
            return [
                'id' => $adRequest->id,
                'product_id' => $adRequest->product_id,
                'product_name' => $adRequest->Product->product_name ?? null,
                'product_image' => $adRequest->Product->product_image ?? null,
                'user_id' => $adRequest->user_id ?? null,
                'status' => $adRequest->status,
                'is_active' => $adRequest->is_active,
                'created_at' => $adRequest->created_at,
            ];
        });

        if ($adRequests->isEmpty()) {
            return response()->error('No ad requests found.', 404);
        }
        return response()->success($adRequests, 'Ad requests retrieved successfully.');
    }

    //get ad request by id
    public function getAdRequestById($id)
    {
        $adRequest = TrendingProducts::with(['product:id,product_name,product_image,user_id', 'approvedBy:id,first_name,last_name', 'rejectedBy:id,first_name,last_name'])->find($id);
        if (!$adRequest) {
            return response()->error('Ad request not found.', 404);
        }

        return response()->success([
            'id' => $adRequest->id,
            'product_id' => $adRequest->product_id,
            'product_name' => $adRequest->Product->product_name ?? null,
            'product_image' => $adRequest->Product->product_image ?? null,
            'user_id' => $adRequest->user_id ?? null,
            'approved_by' => $adRequest->approvedBy->full_name ?? null,
            'rejected_by' => $adRequest->rejectedBy->full_name ?? null,
            'rejection_reason' => $adRequest->rejection_reason,
            'approved_at' => $adRequest->approved_at,
            'rejected_at' => $adRequest->rejected_at,
            'display_order' => $adRequest->display_order,
            'status' => $adRequest->status,
            'is_active' => $adRequest->is_active,
            'created_at' => $adRequest->created_at,
        ], 'Ad request retrieved successfully.');
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

            $adRequest = TrendingProducts::find($id);
            if (!$adRequest) {
                return response()->error('Ad request not found.', 404);
            }

            $status = $request->input('status');
            if ($status === 'approved') {
                $adRequest->approved_by = auth()->user()->id;
                $adRequest->approved_at = now();
            } else if ($status === 'rejected') {
                $adRequest->rejected_by = auth()->user()->id;
                $adRequest->rejected_at = now();
                $adRequest->rejection_reason = $request->input('rejection_reason', null);
            }

            $adRequest->status = $request->input('status');
            $adRequest->is_active = $request->input('is_active');
            $adRequest->display_order = $request->input('display_order', null);
            $adRequest->save();

            return response()->success($adRequest, 'Ad request status updated successfully.');
        } catch (\Exception $e) {
            return response()->error('Failed to update ad request status.', 500, $e->getMessage());
        }
    }
}
