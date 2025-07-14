<?php

namespace App\Http\Controllers;

use App\Models\PaymentGatewayCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentGatewayController extends Controller
{
    public function updatePaymentGateway(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'login_id' => 'required|string',
                'transaction_key' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->error('Validation failed', 422, $validator->errors());
            }

            $validated = $validator->validated();

            $credentials = PaymentGatewayCredential::updateOrCreate(
                ['user_id' => Auth::id(), 'gateway_name' => 'authorizenet'],
                [
                    'login_id' => $validated['login_id'],
                    'transaction_key' => $validated['transaction_key'],
                ]
            );

            return response()->success($credentials, 'Payment gateway credentials saved securely.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while updating payment gateway credentials.', 500, $e->getMessage());
        }
    }

    // Get payment gateway credentials
    public function getPaymentGatewayCredentials()
    {
        try {
            $credentials = PaymentGatewayCredential::where('user_id', Auth::id())
                ->where('gateway_name', 'authorizenet')
                ->first();
            if (!$credentials) {
                return response()->error('No payment gateway credentials found.', 404);
            }
            return response()->success($credentials, 'Payment gateway credentials retrieved successfully.');
        } catch (\Exception $e) {
            return response()->error('An error occurred while retrieving payment gateway credentials.', 500, $e->getMessage());
        }
    }
}
