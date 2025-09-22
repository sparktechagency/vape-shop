<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnectedLocationRequest;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ConnectedLocationController extends Controller
{
    public function storeBranchRequest(ConnectedLocationRequest $request)
    {
        // Validate the request
        $validatedData = $request->validated();
        $planId = $validatedData['plan_id'];

        // Check if the plan exists
        $plan = Plan::find($planId);
        if (!$plan) {
            return response()->error('Plan not found.', 404);
        }
        $planDetails = [[
            'id' => $plan->id,
            'name' => $plan->name,
            'price' => $plan->price,
            'type' => $plan->type,
        ]];


        $result = DB::transaction(function () use ($validatedData, $plan, $planDetails) {

            $branch = auth()->user()->branches()->create([
                'branch_name' => $validatedData['branch_name'],
                'is_active' => false,
            ]);

            $branch->address()->create([
                'address' => $validatedData['address'],
                'region_id' => $validatedData['region_id'],
                'zip_code' => $validatedData['zip_code'] ?? null,
                'latitude' => $validatedData['latitude'] ?? null,
                'longitude' => $validatedData['longitude'] ?? null,
                'location' => (
                    isset($validatedData['latitude'], $validatedData['longitude']) &&
                    $validatedData['latitude'] !== null &&
                    $validatedData['longitude'] !== null
                )
                    ? new Point((float)$validatedData['latitude'], (float)$validatedData['longitude'])
                    : null,
            ]);


            $subscription = $branch->subscriptions()->create([
                'plan_details' => $planDetails,
                'invoice_status' => 'pending_invoice',
                'total_cost' => $plan->price,

            ]);

            return ['branch' => $branch, 'subscription' => $subscription];
        });


        return response()->success([
            'branch' => $result['branch']->load('address'),
            'subscription' => $result['subscription'],
        ], 'Branch and subscription request created successfully. Please complete payment.');
    }

    public function cancelBranchRequest(Branch $branch)
    {

        if (auth()->id() !== $branch->user_id) {
            return response()->error('You are not authorized to perform this action.', 403);
        }
        $subscription =  $branch->subscriptions()->first();
        // dd($subscription);
        if (!$subscription || $subscription->invoice_status !== 'pending_invoice') {
            return response()->error('This branch request cannot be cancelled.', 422);
        }

        try {
            DB::transaction(function () use ($branch) {
                $branch->address()->delete();

                $branch->subscriptions()->delete();

                $branch->delete();
            });
        } catch (\Exception $e) {
            Log::error("Failed to cancel branch request for branch ID {$branch->id}: " . $e->getMessage());
            return response()->error('Could not cancel the branch request. Please try again later.', 500);
        }

        return response()->success(null, 'Branch request has been successfully cancelled.');
    }

    public function getMyBranches(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 15);
            $branches = auth()->user()->branches()
                ->with([
                    'address',
                    'latestSubscription'
                ])
                ->latest()
                ->paginate($perPage);
            if ($branches->isEmpty()) {
                return response()->success(null, 'You have not created any branches yet.');
            }

            return response()->success($branches, 'Branches retrieved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve branches: ' . $e->getMessage());
            return response()->error('An error occurred while fetching branches: ' . $e->getMessage(), 500);
        }
    }

    //activate branch
    public function getActiveBranchesForUser(Request $request, User $user)
    {
        $perPage = $request->query('per_page', 15);

        $branches = $user->branches()
            ->where('is_active', true)
            ->with([
                'address',
                'owner:id,first_name,last_name,avatar,role,email',
            ])
            ->latest()
            ->paginate($perPage);


        if ($branches->isEmpty()) {
            return response()->success(null, 'No active branches found for this user.');
        }

        return response()->success($branches, 'Active branches retrieved successfully.');
    }
}
