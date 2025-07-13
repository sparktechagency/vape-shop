<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoreOrderResource;
use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        try{
            $perPage = request()->get('per_page', 15);
            $storeId = Auth::id();
        $orders = Order::where('store_id', $storeId)
                       ->with('OrderItems.product', 'user')
                       ->latest()
                       ->paginate($perPage);

        return StoreOrderResource::collection($orders)->additional([
            'ok' => true,
            'message' => 'Orders fetched successfully.',
        ]);
        }catch (\Exception $e) {
            return response()->error(
                'Failed to fetch orders',
                500,
                $e->getMessage()
            );
        }
    }


    public function show(Order $order)
    {

        if (Gate::denies('view', $order)) {
            return response()->error(
                'You do not have permission to view this order.',
                403,
                'Unauthorized'
            );
        }

        $order->load('OrderItems.product', 'user', 'checkout');
        return new StoreOrderResource($order);
    }


    public function updateStatus(Request $request, Order $order)
    {
        if (Gate::denies('update', $order)) {
            return response()->error(
                'You do not have permission to update this order.',
                403,
                'Unauthorized'
            );
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected,cancelled,delivered',
        ]);

        if ($validator->fails()) {
                return response()->error(
                    $validator->errors()->first(),
                    422,
                    $validator->errors()
                );
            }

        $validated = $validator->validated();

        $order->status = $validated['status'];
        $order->save();

        // Notify the customer about the order status update
        $customer = $order->user;
        $customer->notify(new OrderStatusUpdatedNotification($order));

        return response()->json([
            'ok' => true,
            'message' => "Order status has been updated to {$order->status}.",
            'data' => new StoreOrderResource($order)
        ]);
    }
}
