<?php

namespace App\Http\Controllers;

use App\Enums\UserRole\Role;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use App\Models\ManageProduct;
use App\Models\Order;
use App\Models\PlatformFee;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\StoreProduct;
use App\Models\WholesalerProduct;
use App\Notifications\NewOrderRequestNotification;
use App\Notifications\OrderRequestConfirmationNotification;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{


    protected $paymentService;
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle the incoming request to place an order.
     *
     * @param  \App\Http\Requests\CheckoutRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function orderRequest(Request $request)

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



    /**
     * Handles the entire B2B order placement process for a single seller.
     */
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'nullable|string|max:255',
            'customer_address' => 'nullable|string',
            'card_details' => 'nullable|array',
            'card_details.card_number' => 'required|string|min:13|max:16',
            'card_details.expiration_month' => 'required|numeric|min:1|max:12',
            'card_details.expiration_year' => 'required|numeric|digits:4',
            'card_details.cvc' => 'required|string|min:3|max:4',
            'cart_items' => 'required|array|min:1',
            'cart_items.*.product_id' => 'required|integer',
            'cart_items.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->error($validator->errors()->first(), 422, $validator->errors());
        }

        $validatedData = $validator->validated();
        $cartItemsInput = $validatedData['cart_items'];
        $buyer = Auth::user();

        $cartProductIds = array_column($cartItemsInput, 'product_id');
        // dd($cartProductIds);
        $productTypeMap = $this->findProductTypesForIds($cartProductIds);


        //find product types for each product ID
        $cartItems = [];
        foreach ($cartItemsInput as $item) {
            if ($productTypeMap->has($item['product_id'])) {
                $item['product_type'] = $productTypeMap->get($item['product_id'])->product_type;
                $cartItems[] = $item;
            }
        }

        //
        if (count($cartItems) !== count($cartItemsInput)) {
            return response()->error('One or more products in your cart are invalid.', 404);
        }
        $cartItems = collect($cartItems);


        $allProducts = collect();
        $cartItems->groupBy('product_type')->each(function ($items, $modelClass) use (&$allProducts) {
            $ids = $items->pluck('product_id');
            if (class_exists($modelClass)) {

                $products = $modelClass::whereIn('id', $ids)->with('b2bPricing')->get();
                $allProducts = $allProducts->merge($products);
            }
        });
        // return $allProducts;
        if ($allProducts->isEmpty()) {
            return response()->error('No valid products found in your cart.', 404);
        }

        $sellerIds = $allProducts->pluck('user_id')->unique();
        if ($sellerIds->count() > 1) {
            return response()->error('You can only order from one seller at a time.', 400);
        }
        $sellerId = $sellerIds->first();
        $seller = User::findOrFail($sellerId);


        DB::beginTransaction();
        try {
            $subTotal = 0;
            $orderItemsData = [];
            $productMap = $allProducts->keyBy('id');

            //b2b pricing validation
            foreach ($cartItems as $item) {
                $product = $productMap->get($item['product_id']);
                if (!$product) continue;


                $connectionExists = $buyer->b2bProviders()->where('provider_id', $sellerId)->where('status', 'approved')->exists();
                if (!$connectionExists) {
                    throw new \Exception("You do not have an approved B2B connection to purchase '{$product->product_name}'.");
                }

                // return $product;
                $b2bPriceRecord = $product->b2bPricing;

                if (!$b2bPriceRecord) {
                    throw new \Exception("A B2B price has not been set for the product '{$product->product_name}'.");
                }


                if ($item['quantity'] < $b2bPriceRecord->moq) {
                    throw new \Exception("The quantity for '{$product->product_name}' does not meet the minimum order requirement of {$b2bPriceRecord->moq}.");
                }

                 $price = $b2bPriceRecord->wholesale_price;
                $subTotal += $price * $item['quantity'];
                $orderItemsData[] = [
                    'productable_id' => $product->id,
                    'productable_type' => get_class($product),
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ];
            }


            $checkout = Checkout::create([
                'user_id' => $buyer->id,
                'checkout_group_id' => 'CHK-' . strtoupper(Str::random(12)),
                'grand_total' => $subTotal,
                'customer_name' => $validatedData['customer_name'],
                'customer_address' => $validatedData['customer_address'],
                'status' => 'pending',
                'type' => 'b2b',
            ]);

            $order = $checkout->orders()->create([
                'store_id' => $sellerId,
                'user_id' => $buyer->id,
                'subtotal' => $subTotal,
                'status' => 'pending',
            ]);

            $order->b2bOrderItems()->createMany($orderItemsData);


            $paymentResponse = $this->paymentService->processPaymentForPayable(
                $order,
                $validatedData['card_details'],
                $seller
            );

            if ($paymentResponse['status'] !== 'success') {
                throw new \Exception("Payment failed. Reason: " . $paymentResponse['message']);
            }


            $order->update(['status' => 'pending']);
            PlatformFee::create(['order_id' => $order->id, 'seller_id' => $sellerId]);
            $seller->notify(new NewOrderRequestNotification($order, $buyer));

            DB::commit();

            return response()->success($checkout->load('orders.b2bOrderItems'), 'Your order has been sent successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->error($e->getMessage(), 400);
        }
    }

    /**
     * Finds the correct model class for a list of product IDs using a single UNION query.
     */
    private function findProductTypesForIds(array $productIds): \Illuminate\Support\Collection
    {
        // Query for manage_products table
        $manageProducts = DB::table('manage_products')
            ->whereIn('id', $productIds)
            ->select('id', DB::raw("'" . addslashes(ManageProduct::class) . "' as product_type"));

        // Query for wholesale_products table
        $wholesaleProducts = DB::table('wholesaler_products')
            ->whereIn('id', $productIds)
            ->select('id', DB::raw("'" . addslashes(WholesalerProduct::class) . "' as product_type"));


        $storeProductsQuery = DB::table('store_products')
            ->whereIn('id', $productIds)
            ->select('id', DB::raw("'" . addslashes(StoreProduct::class) . "' as product_type"))
            ->unionAll($manageProducts)
            ->unionAll($wholesaleProducts);


        return DB::query()->fromSub($storeProductsQuery, 'products')->get()->keyBy('id');
    }
}

