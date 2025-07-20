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

        $admin = User::where('role', Role::ADMIN)->first();


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
}
