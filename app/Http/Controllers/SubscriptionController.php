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
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function getPlans()
    {
        $plans = Plan::all();
        if ($plans->isEmpty()) {
            return response()->error('No plans available', 404);
        }
        return response()->success($plans);
    }

    //update Plans
    public function updatePlan(Request $request, $id)
    {
        try{
            $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'type' => 'required|string|in:main,add_on,location',
            'badge' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $validatedData = $validator->validated();
        $plan = Plan::find($id);
        if (!$plan) {
            return response()->error('Plan not found', 404);
        }

        $plan->name = $validatedData['name'];
        $plan->slug = generateUniqueSlug(Plan::class, $validatedData['name']);
        $plan->price = $validatedData['price'];
        $plan->type = $validatedData['type'];
        $plan->badge = $validatedData['badge'] ?? null;
        $plan->description = $validatedData['description'] ?? $plan->description;
        $plan->save();


        return response()->success($plan, 'Plan updated successfully!');
        } catch (\Exception $e) {
            return response()->error('An error occurred while updating the plan: ' . $e->getMessage(), 500);
        }
    }


    public function processSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'card_details' => 'required|array',
            'card_details.card_number' => 'required|string',
            'card_details.expiration_month' => 'required|numeric',
            'card_details.expiration_year' => 'required|numeric',
            'card_details.cvc' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $validatedData = $validator->validated();
        $plan = Plan::find($validatedData['plan_id']);
        $user = Auth::user();

        $admin = User::where('role', Role::ADMIN->value)->first();


        $paymentResponse = $this->paymentService->processPaymentForPayable($plan, $validatedData['card_details'], $admin);

        if ($paymentResponse['status'] !== 'success') {
            return response()->error('Payment failed: ' . $paymentResponse['message'], 400);
        }

        //expired active subscrib in this user's account
        $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // Monthly plan
            'status' => 'active',
        ]);

        return response()->success(null, 'Subscription activated successfully!');
    }



    // public function processSubscription(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'plan_ids' => 'required|array|min:1',
    //         'plan_ids.*' => 'required|exists:plans,id',
    //         'card_details' => 'required|array',
    //         'card_details.card_number' => 'required|string',
    //         'card_details.expiration_month' => 'required|numeric',
    //         'card_details.expiration_year' => 'required|numeric',
    //         'card_details.cvc' => 'required|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->error($validator->errors()->first(), 422, $validator->errors());
    //     }

    //     $validatedData = $validator->validated();
    //     $planIds = $validatedData['plan_ids'];
    //     $user = Auth::user();

    //     $plans = Plan::whereIn('id', $planIds)->get();

    //     $mainPlansCount = $plans->where('type', 'main')->count();

    //     if ($mainPlansCount > 1) {
    //         return response()->error('You can only select one main subscription plan.', 400);
    //     }
    //     // if ($mainPlansCount === 0 && $plans->where('type', 'add_on')->count() > 0) {
    //     //     return response()->error('You must have a main subscription to add a location.', 400);
    //     // }

    //     $activePlanIds = $user->subscriptions()
    //         ->where('status', 'active')
    //         ->where('ends_at', '>', now())
    //         ->pluck('plan_id')
    //         ->toArray();

    //     $requestedPlanIds = $plans->pluck('id')->toArray();
    //     $alreadySubscribedPlans = array_intersect($requestedPlanIds, $activePlanIds);

    //     if (!empty($alreadySubscribedPlans)) {
    //         $collidingPlanNames = Plan::whereIn('id', $alreadySubscribedPlans)->pluck('name')->implode(', ');
    //         return response()->error("You are already subscribed to the following active plan(s): {$collidingPlanNames}.", 400);
    //     }


    //     $totalPrice = $plans->sum('price');

    //     DB::beginTransaction();
    //     try {

    //         $admin = User::where('role', Role::ADMIN)->firstOrFail();

    //         $user->subscriptions()->where('status', 'active')->update(['status' => 'expired']);

    //         $allNewSubscriptions = [];
    //         foreach ($plans as $plan) {
    //             $subscription = $user->subscriptions()->create([
    //                 'plan_id' => $plan->id,
    //                 'starts_at' => now(),
    //                 'ends_at' => now()->addMonth(),
    //                 'status' => 'pending', // Prothome 'pending' rakha hocche
    //             ]);
    //             $allNewSubscriptions[] = $subscription;
    //         }

    //         // Payment record-ti main subscription-er sathe link kora hocche
    //         $mainSubscription = collect($allNewSubscriptions)->firstWhere('plan.type', 'main');
    //         $targetSubscription = $mainSubscription ?? $allNewSubscriptions[0];

    //         // Ekhon shothik Eloquent Model (`$targetSubscription`) pathano hocche
    //         $paymentResponse = $this->paymentService->processPaymentForPayable($targetSubscription, $validatedData['card_details'], $admin);

    //         if ($paymentResponse['status'] !== 'success') {
    //             // Payment fail korle transaction rollback hoye jabe
    //             throw new \Exception('Payment failed: ' . $paymentResponse['message']);
    //         }

    //         // Payment shofol hole, shobgulo notun subscription 'active' kora hocche
    //         foreach ($allNewSubscriptions as $sub) {
    //             $sub->update(['status' => 'active']);
    //         }

    //         DB::commit();

    //         return response()->success(null, 'Subscription activated successfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->error('Failed to process subscription: ' . $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine(), 500, [
    //             'exception' => get_class($e),
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //     }
    // }
}
