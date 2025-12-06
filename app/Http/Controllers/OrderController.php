<?php

namespace App\Http\Controllers;

use App\Http\Resources\StoreOrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreProduct;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        try {
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
        } catch (\Exception $e) {
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


    public function sellerUpdateOrder(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|integer',
            'cart_items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {

            $order = Order::where('id', $orderId)
                ->where('store_id', Auth::id())
                ->with('checkout')
                ->firstOrFail();

            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Cannot update order at this stage.'], 403);
            }
            $cartItems = $request->cart_items;
            $productIds = array_column($cartItems, 'product_id');

            $products = StoreProduct::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($products as $product) {
                if ($product->user_id !== Auth::id()) {
                    throw new \Exception("You cannot add products from other stores.");
                }
            }

            $newSubTotal = 0;
            $newItemsData = [];


            $taxRate = Auth::user()->tax_percentage ?? 0;

            foreach ($cartItems as $item) {
                if (!isset($products[$item['product_id']])) continue;

                $product = $products[$item['product_id']];
                $lineTotal = $product->product_price * $item['quantity'];
                $newSubTotal += $lineTotal;

                $newItemsData[] = [
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->product_price,
                ];
            }

            $newTaxAmount = ($newSubTotal * $taxRate) / 100;
            $checkout = $order->checkout;

            $oldOrderTotal = $order->subtotal + $order->tax_amount;
            $newOrderTotal = $newSubTotal + $newTaxAmount;

            $checkoutGrandTotal = ($checkout->grand_total - $oldOrderTotal) + $newOrderTotal;


            $order->orderItems()->delete();

            OrderItem::insert($newItemsData);


            $order->update([
                'subtotal' => $newSubTotal,
                'tax_amount' => $newTaxAmount
            ]);

            $checkout->update([
                'grand_total' => $checkoutGrandTotal
            ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Order updated successfully by seller.',
                'new_total' => $newOrderTotal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
