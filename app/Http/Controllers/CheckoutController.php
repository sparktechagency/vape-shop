<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreProduct;
use App\Notifications\NewOrderRequestNotification;
use App\Notifications\OrderRequestConfirmationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function orderRequest(CheckoutRequest $request)
    {

        $validatedData = $request->validated();
        $cartItems = $validatedData['cart_items'];



        DB::beginTransaction();

        try {

            $products = StoreProduct::whereIn('id', array_column($cartItems, 'product_id'))->get()->keyBy('id');
            $groupedByStore = [];
            $grandTotal = 0;
            // dd($products);
            // return $cartItems;
            foreach ($cartItems as $item) {
                $product = $products[$item['product_id']];
                // return $product;
                $storeId = $product->user_id; // Assuming the store ID is the user ID of the product

                if (!isset($groupedByStore[$storeId])) {
                    $groupedByStore[$storeId] = [
                        'items' => [],
                        'sub_total' => 0,
                        'store_owner' => $product->user,
                    ];
                }

                $lineTotal = $product->product_price * $item['quantity'];
                $groupedByStore[$storeId]['items'][] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->product_price,
                ];
                $groupedByStore[$storeId]['sub_total'] += $lineTotal;
                $grandTotal += $lineTotal;
            }


            $checkout = Checkout::create([
                'user_id' => Auth::id(),
                'checkout_group_id' => 'VSM-' . strtoupper(Str::random(12)),
                'grand_total' => $grandTotal,
                'customer_name' => $validatedData['customer_name'],
                'customer_email' => $validatedData['customer_email'],
                'customer_phone' => $validatedData['customer_phone'],
                'customer_dob' => $validatedData['customer_dob'] ? \Carbon\Carbon::createFromFormat('d-m-Y', $validatedData['customer_dob']) : null,
                'customer_address' => $validatedData['customer_address'],
                'status' => 'pending',
            ]);


            // return $groupedByStore;
            foreach ($groupedByStore as $storeId => $storeData) {
                $order = Order::create([
                    'checkout_id' => $checkout->id,
                    'store_id' => $storeId,
                    'user_id' => Auth::id(),
                    'subtotal' => $storeData['sub_total'],
                    'status' => 'pending',
                ]);


                foreach ($storeData['items'] as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                    ]);
                }


                $storeOwner = $storeData['store_owner'];
                // return $storeOwner;
                if ($storeOwner) {
                    $storeOwner->notify(new NewOrderRequestNotification($order, Auth::user()));
                }
            }

            if ($checkout) {
                Auth::user()->notify(new OrderRequestConfirmationNotification($checkout));
            }


            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Your order request has been sent to all stores successfully.',
                'checkout_id' => $checkout->checkout_group_id,
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'An error occurred while placing your order. Please try again.',
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }


    public function index()
    {
        try {
            $perPage = request()->get('per_page', 10);
            $checkouts = Auth::user()->checkouts()->latest()->paginate($perPage);
            return CheckoutResource::collection($checkouts);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to fetch checkouts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Checkout $checkout)
    {

        if (Auth::id() !== $checkout->user_id) {
            abort(403);
        }

        $checkout->load('orders.store', 'orders.OrderItems.product');
        return new CheckoutResource($checkout);
    }
}
