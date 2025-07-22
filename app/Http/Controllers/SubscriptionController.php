<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\SubscriptionPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{

    public function getSubscriptionsPlan(Request $request)
    {
        try {
            $selectedTypes = $request->query('type', []);
            $query = Plan::query();
            if (!empty($selectedTypes)) {
                $query->whereIn('type', $selectedTypes);
            }
            $plans = $query->get();
            if ($plans->isEmpty()) {
                return response()->error('No plans available', 404);
            }
            return response()->success($plans);
        } catch (\Exception $e) {
            return response()->error('An error occurred while fetching plans: ' . $e->getMessage(), 500);
        }
    }

    public function processSubscriptionRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_ids' => 'required|array|min:1',
            'plan_ids.*' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $planIds = $validator->validated()['plan_ids'];
        $user = Auth::user();

        $plans = Plan::whereIn('id', $planIds)->get();
        $totalPrice = $plans->sum('price');


        $planDetails = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'type' => $plan->type,
                'badge' => $plan->badge,
            ];
        });

        $subscription =  $user->subscriptions()->create([
            'plan_details' => $planDetails,
            'total_cost' => $totalPrice,
            'invoice_status' => 'pending_invoice',
        ]);

        //send notification to admin
        $admin = User::where('role', Role::ADMIN)->get();
        foreach ($admin as $adminUser) {
            $adminUser->notify(new \App\Notifications\SubscriptionRequestReceivedNotification($subscription));
        }

        //send notification to user
        $user->notify(new \App\Notifications\SubscriptionRequestConfirmation($subscription));

        return response()->success(null, 'Your subscription request has been submitted. You will receive an invoice via email shortly.');
    }
}
