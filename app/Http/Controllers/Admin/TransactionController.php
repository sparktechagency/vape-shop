<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\MostFollowerAd;
use App\Models\Payment;
use App\Models\TrendingProducts;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    //transaction history
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $transactions = Payment::with(['payable' => function ($morphTo) {

                $morphTo->morphWith([

                    TrendingProducts::class => [
                        'user:id,first_name,last_name,email,role',
                        'product:id,product_name,user_id',
                    ],
                    MostFollowerAd::class => [
                        'user:id,first_name,last_name,email,role',
                    ],
                    // Order::class => ['customer:id,name'],
                ]);
            }])
                ->latest()
                ->paginate((int)$perPage);

            if ($transactions->isEmpty()) {
                return response()->error('No transactions found', 404);
            }

            return TransactionResource::collection($transactions)
                ->additional([
                    'ok' => true,
                    'message' => 'Transaction history retrieved successfully.',
                    'status' => 200,
                ]);
        } catch (\InvalidArgumentException $e) {
            return response()->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return response()->error('Error retrieving transaction history', 500, $e->getMessage());
        }
    }
}
