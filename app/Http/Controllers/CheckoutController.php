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


    public function placeOrder(Request $request)
    {
        $validatedData = $request->validate([
            'customer_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            'card_details' => 'required|array',
            'card_details.card_number' => 'required|string|min:13|max:16',
            'card_details.expiration_month' => 'required|numeric|min:1|max:12',
            'card_details.expiration_year' => 'required|numeric|digits:4',
            'card_details.cvc' => 'required|string|min:3|max:4',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|integer',
            'cart_items.*.product_type' => 'required|string|in:App\Models\ManageProduct,App\Models\WholesalerProduct,App\Models\StoreProduct',
            'cart_items.*.quantity' => 'required|integer|min:1',
        ]);

        $cardDetails = $validatedData['card_details'];
        $cartItems = $validatedData['cart_items'];

        DB::beginTransaction();

        try {
            $groupedBySeller = $this->groupCartBySeller($cartItems);

            if (empty($groupedBySeller)) {
                throw new \Exception('Could not find valid products or pricing for the items in your cart.');
            }

            $grandTotal = array_sum(array_column($groupedBySeller, 'sub_total'));

            $checkout = Checkout::create([
                'user_id' => Auth::id(),
                'checkout_group_id' => 'CHK-' . strtoupper(Str::random(12)),
                'grand_total' => $grandTotal,
                'customer_name' => $validatedData['customer_name'],
                'delivery_address' => $validatedData['delivery_address'],
                'status' => 'pending',
            ]);

            foreach ($groupedBySeller as $sellerId => $sellerData) {
                $seller = User::find($sellerId);

                $order = $checkout->orders()->create([
                    'store_id' => $sellerId,
                    'user_id' => Auth::id(),
                    'sub_total' => $sellerData['sub_total'],
                    'status' => 'pending_payment',
                ]);

                $order->items()->createMany($sellerData['items']);

                $paymentResponse = $this->paymentService->processPaymentForPayable($order, $cardDetails, $seller);

                if ($paymentResponse['status'] !== 'success') {
                    throw new \Exception("Payment failed for seller ID: {$sellerId}. Reason: " . $paymentResponse['message']);
                }

                $order->update(['status' => 'pending_approval']);
                PlatformFee::create(['order_id' => $order->id, 'seller_id' => $sellerId]);

                if ($seller) {
                    $seller->notify(new NewOrderRequestNotification($order, Auth::user()));
                }
            }

            Auth::user()->notify(new OrderRequestConfirmation($checkout));

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Your order request has been sent successfully.',
                'checkout_id' => $checkout->checkout_group_id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function groupCartBySeller(array $cartItems): array
    {
        $groupedBySeller = [];
        $productModels = [];
        foreach ($cartItems as $item) {
            $productModels[$item['product_type']][] = $item['product_id'];
        }

        $allProducts = collect();
        foreach ($productModels as $modelClass => $ids) {
            if (class_exists($modelClass)) {
                $products = $modelClass::whereIn('id', $ids)->with('b2bPricing')->get();
                $allProducts = $allProducts->merge($products);
            }
        }

        $productMap = $allProducts->keyBy(fn($p) => get_class($p) . '-' . $p->id);
        $buyer = Auth::user();

        foreach ($cartItems as $item) {
            $productKey = $item['product_type'] . '-' . $item['product_id'];
            $product = $productMap->get($productKey);

            if (!$product) continue;

            // ডাইনামিক প্রাইসিং লজিক (B2B কানেকশনের উপর ভিত্তি করে)
            $connectionExists = $buyer->b2bProviders()->where('provider_id', $product->seller_id)->where('status', 'approved')->exists();
            $price = ($connectionExists && $product->b2bPricing) ? $product->b2bPricing->wholesale_price : $product->price;

            $sellerId = $product->seller_id;
            if (!isset($groupedBySeller[$sellerId])) {
                $groupedBySeller[$sellerId] = ['items' => [], 'sub_total' => 0];
            }

            $lineTotal = $price * $item['quantity'];

            $groupedBySeller[$sellerId]['items'][] = [
                'productable_id' => $product->id,
                'productable_type' => get_class($product),
                'quantity' => $item['quantity'],
                'price' => $price,
            ];
            $groupedBySeller[$sellerId]['sub_total'] += $lineTotal;
        }

        return $groupedBySeller;
    }
}
