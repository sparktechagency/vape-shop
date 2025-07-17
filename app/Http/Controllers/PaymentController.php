<?php

namespace App\Http\Controllers;

use App\Models\ManageProduct;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\TrendingProducts;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function payForOrder(Request $request, ManageProduct $product)
    {
        // $user = Auth::user();
        // if ($user->id !== $product->user_id) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized. You do not own this product.'], 403);
        // }

        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string|min:13|max:16',
            'expiration_month' => 'required|numeric|between:1,12',
            'expiration_year' => 'required|numeric|digits:4|date_format:Y|after_or_equal:today',
            'cvc' => 'required|string|min:3|max:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $cardDetails = $validator->validated();

        $trendingRequest = TrendingProducts::create([
            'product_id' => 2,
            // 'payments_id'   => 1,
            'amount'     => '500.00',
            'status'     => 'pending',
        ]);
        dd($trendingRequest);
        $trendingRequest['amount'] = 500.00;

        $response = $this->paymentService->processPaymentForPayable($trendingRequest, $cardDetails);

        if ($response['status'] === 'success') {
            return response()->success(null, $response['message'], 200);
        }

        return response()->error($response['message'], 422);
    }
}
