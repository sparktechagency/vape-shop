<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\InvoiceSentNotification;
use App\Notifications\SubscriptionActivatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{



    //update Plans
    public function updatePlan(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'type' => 'required|string|in:member,store,brand,wholesaler,advocacy,hemp,location',
                'badge' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'features' => 'nullable|array',
                'features.*' => 'string|max:255',
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
            $plan->features = $validatedData['features'] ?? $plan->features;
            $plan->save();


            return response()->success($plan, 'Plan updated successfully!');
        } catch (\Exception $e) {
            return response()->error('An error occurred while updating the plan: ' . $e->getMessage(), 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 20);
            $query = Subscription::with('user:id,first_name,last_name,avatar,role,email');

            if ($request->has('status')) {
                $query->where('invoice_status', $request->status);
            }

            $subscriptions = $query->latest()->paginate($perPage);

            if ($subscriptions->isEmpty()) {
                return response()->error('No subscriptions found', 404);
            }

            return response()->success($subscriptions);
        } catch (\Exception $e) {
            return response()->error('An error occurred while fetching subscriptions: ' . $e->getMessage(), 500);
        }
    }


    public function show(Subscription $subscription)
    {
        try {
            $subscription =  $subscription->load('user');
            if (!$subscription) {
                return response()->error('Subscription not found', 404);
            }

            return response()->success($subscription);
        } catch (\Exception $e) {
            return response()->error('An error occurred while fetching the subscription: ' . $e->getMessage(), 500);
        }
    }


    public function updateInvoiceStatus(Request $request, Subscription $subscription)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending_invoice,invoice_sent,paid,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422);
        }

        $newStatus = $validator->validated()['status'];
        $oldStatus = $subscription->invoice_status;
        // dd($oldStatus, $newStatus);
        $subscription->invoice_status = $newStatus;

        if ($newStatus === 'paid' && $oldStatus !== 'paid') {

            if (is_null($subscription->starts_at)) {
                $subscription->starts_at = now();
                $subscription->ends_at = now()->addMonth();
            }
            try {
                $subscription->user->notify(new SubscriptionActivatedNotification($subscription));
            } catch (\Exception $e) {
                Log::error("Failed to send subscription activation notification for subscription ID {$subscription->id}: " . $e->getMessage());
            }
        }

        $subscription->save();

        // dd($subscription->user);

        if ($newStatus === 'invoice_sent' && $oldStatus !== 'invoice_sent') {
            $testMail = $subscription->user->notify(new InvoiceSentNotification($subscription));
            Log::info($testMail);
        }

        return response()->success($subscription, 'Invoice status updated successfully.');
    }
}
